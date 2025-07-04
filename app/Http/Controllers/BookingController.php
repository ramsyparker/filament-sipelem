<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;
use App\Notifications\PaymentStatusNotification;
use App\Notifications\BookingStatusNotification;
use App\Notifications\NewBookingNotification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\OrderResource;

class BookingController extends Controller
{
    /**
     * Tampilkan form booking untuk lapangan tertentu
     */
    public function showBookingForm($fieldId)
    {
        $field = Field::findOrFail($fieldId);

        $selectedDate = request('booking_date'); // Ambil tanggal dari query param

        $query = DB::table('schedules')
            ->where('field_id', $fieldId)
            ->where('status', 'available');

        if ($selectedDate) {
            $query->whereDate('start_time', $selectedDate);
        } else {
            $today = Carbon::now()->toDateString();
            $query->whereDate('start_time', $today);
        }

        $availableSchedules = $query->orderBy('start_time')->paginate(16);

        return view('booking', [
            'field' => $field,
            'availableSchedules' => $availableSchedules,
        ]);
    }

    // Konversi nama hari Indonesia ke bahasa Inggris
    private static function getEnglishDay($indonesianDay)
    {
        $days = [
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
            'Sabtu' => 'Saturday',
            'Minggu' => 'Sunday',
        ];

        return $days[$indonesianDay] ?? null;
    }

    /**
     * Simpan booking baru dari form
     */
    public function store(Request $request)
    {
        $request->validate([
            'field_id' => 'required|exists:fields,id',
            'booking_date' => 'required|date|after_or_equal:today', // Pastikan tanggal booking lebih dari atau sama dengan hari ini
            'start_time' => 'required|date_format:H:i', // Format waktu mulai yang benar
            'duration' => 'required|integer|min:1|max:5', // Durasi booking dalam jam
        ]);

        $user = Auth::user();
        $field = Field::findOrFail($request->field_id);

        // Menggunakan input dari form untuk tanggal dan waktu
        $bookingDate = Carbon::parse($request->booking_date);
        // Mengambil waktu mulai dari form
        $startTime = Carbon::parse($request->start_time);

        // Pastikan durasi dalam bentuk integer, jika perlu, ubah menjadi integer
        $duration = (int) $request->duration;

        // Menghitung waktu selesai berdasarkan durasi (dalam jam)
        $endTime = $startTime->copy()->addHours($duration);


        // Cek apakah waktu tersebut sudah terbooking
        $isConflict = Booking::where('field_id', $request->field_id)
            ->where('booking_date', $request->booking_date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime->format('H:i:s'))
                        ->where('end_time', '>', $startTime->format('H:i:s'));
                });
            })
            ->exists();

        if ($isConflict) {
            return back()->with('error', 'Waktu yang dipilih sudah terbooking.');
        }
        // Mengambil order_id yang unik
        $orderId = uniqid('ORDER-');  // ID unik, misal ORDER-12345

        // Simpan booking baru
        $booking = Booking::create([
            'user_id' => $user->id,
            'field_id' => $request->field_id,
            'booking_date' => $bookingDate->format('Y-m-d'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'status' => 'pending', // Status booking masih pending sampai pembayaran diverifikasi
            'price' => $field->price * $request->duration, // Harga lapangan * durasi
            'order_id' => $orderId, // Atau gunakan sistem order_id lain
        ]);

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PROD');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Membuat transaksi untuk Midtrans
        $transaction_details = [
            'order_id' => $orderId,
            'gross_amount' => $booking->price, // Total yang harus dibayar
        ];

        $customer_details = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];

        $items_details = [
            [
                'id' => 'field_' . $field->id,
                'price' => $field->price * $request->duration,
                'quantity' => 1,
                'name' => $field->name,
            ]
        ];

        $transaction_data = [
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $items_details,
        ];
        try {
            // Generate Snap Token
            $snapToken = Snap::getSnapToken($transaction_data);

            // Update payment dengan snap_token
            $payment = Payment::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'amount' => $booking->price,
                'status' => 'pending', // Status pembayaran sementara sebelum Midtrans memverifikasi
                'payment_method' => 'midtrans', // Metode pembayaran (Midtrans)
                'payment_token' => $snapToken,
            ]);

            // Kirim notifikasi ke admin/owner untuk booking baru
            $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
            foreach ($admins as $admin) {
                // Kirim notifikasi email
                $admin->notify(new NewBookingNotification($booking, $user, $field));

                // Kirim notifikasi ke panel Filament
                FilamentNotification::make()
                    ->title('Booking baru dari ' . $user->name)
                    ->icon('heroicon-o-shopping-cart')
                    ->body('Order ID: ' . $orderId . ' untuk lapangan ' . $field->name . ' pada ' . $booking->booking_date . ' jam ' . $startTime->format('H:i'))
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->label('View')
                            ->url(url('/' . ($admin->role === 'owner' ? 'owner' : 'admin') . '/orders/' . $payment->id))
                    ])
                    ->sendToDatabase($admin);
            }

            return redirect()->route('booking.payment', ['snap_token' => $snapToken, 'order_id' => $orderId]);
        } catch (\Exception $e) {
            return back()->with('error', 'Pembayaran gagal. Coba lagi.' . $e->getMessage());
        }
    }
    public function payment(Request $request)
    {
        // Mengambil snap_token dan order_id dari parameter URL
        $snapToken = $request->snap_token;
        $orderId = $request->order_id;
        // Cetak data untuk melihat apakah diteruskan dengan benar


        // Jika snap_token atau order_id tidak ada, beri pesan error
        if (!$snapToken || !$orderId) {
            return back()->with('error', 'Token pembayaran atau Order ID tidak ditemukan.');
        }
        // Ambil data pembayaran berdasarkan snap_token
        $payment = Payment::where('payment_token', $snapToken)->first();

        // Cari data booking berdasarkan order_id
        $booking = Booking::where('order_id', $orderId)->first();

        if (!$booking) {
            return back()->with('error', 'Data booking tidak ditemukan.');
        }

        // Ambil data lapangan (Field) terkait dengan booking
        $field = $booking->field; // Asumsikan ada relasi antara booking dan field
        // Ambil hanya tanggal dari booking_date (format: Y-m-d)
        $cleanDate = Carbon::parse($booking->booking_date)->toDateString(); // contoh hasil: '2025-05-08'

        // Ambil hanya jam dan menit dari start_time dan end_time (format: H:i)
        $cleanStartTime = Carbon::parse($booking->start_time)->format('H:i');
        $cleanEndTime   = Carbon::parse($booking->end_time)->format('H:i');
        // Gabungkan booking_date dan start_time menjadi start_time lengkap (datetime)
        $startString = $cleanDate . ' ' . $cleanStartTime; // Menggabungkan tanggal dan waktu mulai
        $endString = $cleanDate . ' ' . $cleanEndTime; // Menggabungkan tanggal dan waktu selesai

        // Debugging output untuk memastikan string valid
        // Tambahkan debug untuk memeriksa format string gabungan
        // dd([
        //     'user_id' => $booking->user_id,
        //     'field_id' => $booking->field_id,
        //     'startString' => $startString,
        //     'endString' => $endString,
        //     'booking_date' => $booking->booking_date,
        //     'start_time' => $booking->start_time,
        //     'end_time' => $booking->end_time
        // ]);

        try {
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $startString);
            $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $endString);
            // Parsing end time
        } catch (\Exception $e) {
            return back();
        }


        $schedules = Schedule::where('field_id', $booking->field_id)
            // Cek apakah start_time lebih besar atau sama dengan waktu mulai
            ->where('start_time', '>=', $startDateTime)
            // Cek apakah end_time lebih kecil atau sama dengan waktu selesai
            ->where('end_time', '<=', $endDateTime)
            ->get();

        foreach ($schedules as $schedule) {
            if ($schedule->status === 'booked') {
                return back()->with('error', 'Salah satu jam yang Anda pilih sudah dibooking pengguna lain.');
            }
        }

        // Jika semua aman, lanjut update
        foreach ($schedules as $schedule) {
            $schedule->status = 'booked';
            $schedule->booked_by_user_id = $booking->user_id;
            $schedule->save();
        }

        $booking->status = 'completed';
        $booking->save();




        if (!$payment) {
            return back()->with('error', 'Data pembayaran atau booking tidak ditemukan.');
        }

        $payment->status = 'completed';
        $payment->save();




        // Kirim data ke tampilan
        return view('booking-payment', [
            'snapToken' => $snapToken,
            'orderId' => $orderId,
            'field' => $field,
            'bookingDate' => $booking->booking_date->format('d M Y'),  // Mengirim tanggal booking
            'startTime' => $booking->start_time->format('H:i'),  // Mengirim waktu mulai
            'endTime' => $booking->end_time->format('H:i'),  // Mengirim waktu selesai
            'amount' => $payment->amount,

        ]);
    }

    public function notification(Request $request)
    {
        // Inisialisasi notifikasi dari Midtrans
        $notification = new Notification();

        // Ambil data status transaksi dari Midtrans
        $transactionStatus = $notification->transaction_status;  // Status transaksi
        $orderId = $notification->order_id;  // Mengakses order_id dari notifikasi

        // Cari data pembayaran berdasarkan order_id
        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Cari data booking berdasarkan order_id
        $booking = Booking::where('order_id', $orderId)->first();

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $user = $booking->user;
        $field = $booking->field;

        $notifMessage = null;
        $notifType = 'info';

        // Cek status transaksi dari Midtrans
        switch ($transactionStatus) {
            case 'settlement': // Pembayaran berhasil
                $payment->status = 'completed';
                $payment->save();
                $booking->status = 'completed';
                $booking->save();

                // Kirim notifikasi ke admin/owner menggunakan Filament
                $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
                foreach ($admins as $admin) {
                    // Kirim notifikasi email
                    $admin->notify(new PaymentStatusNotification('completed', $orderId, $payment->amount));
                    $admin->notify(new BookingStatusNotification('completed', $orderId, $field->name, $booking->booking_date->format('d M Y'), $booking->start_time->format('H:i'), $booking->end_time->format('H:i')));

                    // Kirim notifikasi ke panel Filament
                    FilamentNotification::make()
                        ->title('Pembayaran Berhasil')
                        ->body('Order ID: ' . $orderId . ' - Rp ' . number_format($payment->amount, 0, ',', '.'))
                        ->success()
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->label('View Order')
                                ->url(url('/' . ($admin->role === 'owner' ? 'owner' : 'admin') . '/orders/' . $payment->id))
                        ])
                        ->sendToDatabase($admin);

                    FilamentNotification::make()
                        ->title('Booking Dikonfirmasi')
                        ->body('Order ID: ' . $orderId . ' - ' . $field->name . ' pada ' . $booking->booking_date->format('d M Y') . ' jam ' . $booking->start_time->format('H:i'))
                        ->success()
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->label('View Order')
                                ->url(url('/' . ($admin->role === 'owner' ? 'owner' : 'admin') . '/orders/' . $payment->id))
                        ])
                        ->sendToDatabase($admin);
                }
                $notifMessage = 'Pembayaran berhasil! Booking Anda telah dikonfirmasi.';
                $notifType = 'success';
                break;

            case 'pending': // Pembayaran masih pending
                $payment->status = 'pending';
                $payment->save();

                // Kirim notifikasi ke admin/owner menggunakan Filament
                $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
                foreach ($admins as $admin) {
                    // Kirim notifikasi email
                    $admin->notify(new PaymentStatusNotification('pending', $orderId, $payment->amount));
                    $admin->notify(new BookingStatusNotification('pending', $orderId, $field->name, $booking->booking_date->format('d M Y'), $booking->start_time->format('H:i'), $booking->end_time->format('H:i')));

                    // Kirim notifikasi ke panel Filament
                    FilamentNotification::make()
                        ->title('Pembayaran Pending')
                        ->body('Order ID: ' . $orderId . ' - Rp ' . number_format($payment->amount, 0, ',', '.'))
                        ->warning()
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->label('View Order')
                                ->url(url('/' . ($admin->role === 'owner' ? 'owner' : 'admin') . '/orders/' . $payment->id))
                        ])
                        ->sendToDatabase($admin);

                    FilamentNotification::make()
                        ->title('Booking Pending')
                        ->body('Order ID: ' . $orderId . ' - ' . $field->name . ' pada ' . $booking->booking_date->format('d M Y') . ' jam ' . $booking->start_time->format('H:i'))
                        ->warning()
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->label('View Order')
                                ->url(url('/' . ($admin->role === 'owner' ? 'owner' : 'admin') . '/orders/' . $payment->id))
                        ])
                        ->sendToDatabase($admin);
                }
                $notifMessage = 'Menunggu konfirmasi pembayaran...';
                $notifType = 'info';
                break;

            case 'deny':
            case 'expire':
            case 'cancel': // Pembayaran gagal
                $payment->status = 'failed';
                $payment->save();
                $booking->status = 'failed';
                $booking->save();

                // Kirim notifikasi ke admin/owner menggunakan Filament
                $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
                foreach ($admins as $admin) {
                    // Kirim notifikasi email
                    $admin->notify(new PaymentStatusNotification('failed', $orderId, $payment->amount));
                    $admin->notify(new BookingStatusNotification('failed', $orderId, $field->name, $booking->booking_date->format('d M Y'), $booking->start_time->format('H:i'), $booking->end_time->format('H:i')));

                    // Kirim notifikasi ke panel Filament
                    FilamentNotification::make()
                        ->title('Pembayaran Gagal')
                        ->body('Order ID: ' . $orderId . ' - Rp ' . number_format($payment->amount, 0, ',', '.'))
                        ->danger()
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->label('View Order')
                                ->url(url('/' . ($admin->role === 'owner' ? 'owner' : 'admin') . '/orders/' . $payment->id))
                        ])
                        ->sendToDatabase($admin);

                    FilamentNotification::make()
                        ->title('Booking Gagal')
                        ->body('Order ID: ' . $orderId . ' - ' . $field->name . ' pada ' . $booking->booking_date->format('d M Y') . ' jam ' . $booking->start_time->format('H:i'))
                        ->danger()
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->label('View Order')
                                ->url(url('/' . ($admin->role === 'owner' ? 'owner' : 'admin') . '/orders/' . $payment->id))
                        ])
                        ->sendToDatabase($admin);
                }
                $notifMessage = 'Pembayaran gagal! Silakan coba lagi.';
                $notifType = 'error';
                break;
            default:
                $payment->status = 'unknown';
                $payment->save();
                $notifMessage = 'Status pembayaran tidak dikenali.';
                $notifType = 'warning';
                break;
        }

        // Jika request dari browser (bukan callback Midtrans), redirect ke halaman utama dengan flash
        if ($request->expectsJson()) {
            return response()->json('OK');
        } else {
            return redirect('/')->with(['success' => $notifMessage, 'notif_type' => $notifType]);
        }
    }

    /**
     * Tampilkan riwayat booking milik user yang sedang login
     */
    public function history()
    {
        $user = Auth::user();
        $bookings = Booking::with('field')
            ->where('user_id', $user->id)
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->get();
        return view('booking-history', compact('bookings'));
    }

    /**
     * Tampilkan detail booking untuk admin/owner
     */
    public function showDetail($order_id)
    {
        $booking = Booking::with(['user', 'field', 'payment'])->where('order_id', $order_id)->firstOrFail();
        return view('booking-detail', compact('booking'));
    }
}
