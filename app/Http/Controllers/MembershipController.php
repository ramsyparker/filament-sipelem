<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Membership;
use App\Models\UserMembership;
use App\Models\Payment; // Menambahkan Payment Model
use App\Models\Field;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;
use App\Notifications\PaymentStatusNotification;
use App\Notifications\BookingStatusNotification;
use App\Notifications\NewMembershipNotification;
use Filament\Actions\Modal\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Actions\Modal\Actions\Action as ActionsAction;

class MembershipController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'membership_id' => 'required|exists:memberships,id',
            'field_id' => 'required|exists:fields,id',
            'day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'booking_time' => 'required|date_format:H:i',
        ]);

        $membership = Membership::findOrFail($request->membership_id);

        // Membuat waktu pemesanan berdasarkan waktu yang dipilih oleh pengguna
        $bookingTime = Carbon::parse($request->booking_time);

        // Menyiapkan data untuk Midtrans
        $user = Auth::user();
        $totalAmount = $membership->price; // Harga membership yang akan dibayar

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PROD');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Membuat transaksi untuk Midtrans
        $transaction_details = [
            'order_id' => 'ORDER-' . strtoupper(uniqid()),
            'gross_amount' => $totalAmount,
        ];

        $customer_details = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];

        $items_details = [
            [
                'id' => 'membership_' . $membership->id,
                'price' => $membership->price,
                'quantity' => 1,
                'name' => $membership->name,
            ]
        ];

        $transaction_data = [
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $items_details,
        ];

        // Create Snap transaction (Midtrans)
        try {
            $snapToken = Snap::getSnapToken($transaction_data);
        } catch (\Exception $e) {
            return back()->with('error', 'Pembayaran gagal. Coba lagi.');
        }

        // Membuat keanggotaan pengguna dan booking untuk setiap minggu sesuai hari yang dipilih
        $userMembership = UserMembership::create([
            'user_id' => $user->id,
            'membership_id' => $membership->id,
            'field_id' => $request->field_id,
            'booking_time' => $bookingTime,
            'day_of_week' => $request->day_of_week,
            'start_date' => now(),
            'end_date' => now()->addDays($membership->duration), // Sesuaikan durasi membership
            'status' => 'active',
            'order_id' => $transaction_details['order_id'], // Menyimpan order_id yang unik
        ]);

        // Ambil data field untuk notifikasi
        $field = Field::find($request->field_id);

        // Kirim notifikasi ke admin/owner untuk membership baru
        $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
        foreach ($admins as $admin) {
            // Kirim notifikasi email
            $admin->notify(new NewMembershipNotification($userMembership, $user, $membership, $field));

            // Kirim notifikasi ke panel Filament
            FilamentNotification::make()
                ->title('Membership baru dari ' . $user->name)
                ->icon('heroicon-o-identification')
                ->body('Membership: ' . $membership->name . ' di lapangan ' . $field->name . ' - Order ID: ' . $transaction_details['order_id'])
                ->success()
                ->sendToDatabase($admin);
        }

        // Menyimpan data pembayaran ke tabel payments
        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => $transaction_details['order_id'],
            'amount' => $totalAmount,
            'status' => 'pending', // Status pembayaran sementara sebelum Midtrans memverifikasi
            'payment_method' => 'midtrans', // Metode pembayaran (dalam hal ini Midtrans)
            'payment_token' => $snapToken,
        ]);

        // Memetakan hari yang dipilih pengguna ke konstanta Carbon
        $daysOfWeek = [
            'Sunday' => Carbon::SUNDAY,
            'Monday' => Carbon::MONDAY,
            'Tuesday' => Carbon::TUESDAY,
            'Wednesday' => Carbon::WEDNESDAY,
            'Thursday' => Carbon::THURSDAY,
            'Friday' => Carbon::FRIDAY,
            'Saturday' => Carbon::SATURDAY,
        ];

        // Menghitung tanggal pemesanan pertama sesuai dengan hari yang dipilih pengguna
        $firstBookingDay = now()->next($daysOfWeek[$request->day_of_week]);

        // Menghitung durasi dalam minggu untuk menentukan jumlah pemesanan
        $endDate = now()->addDays($membership->duration);
        $weeksToBook = $firstBookingDay->diffInWeeks($endDate);

        // Cek apakah sudah ada pemesanan pada tanggal yang sama
        $existingBooking = Booking::where('field_id', $request->field_id)
            ->where('booking_date', $firstBookingDay->format('Y-m-d'))
            ->where('start_time', $bookingTime->format('H:i:s'))
            ->first();

        // Jika sudah ada booking pada waktu yang dipilih, tidak perlu membuat booking tambahan
        if (!$existingBooking) {
            // Membuat entri pemesanan untuk setiap minggu
            for ($i = 0; $i < $weeksToBook; $i++) {
                $bookingDate = $firstBookingDay->copy()->addDays(7 * $i);  // Menggunakan addDays untuk interval 7 hari

                // Membuat pemesanan hanya untuk hari pertama yang dipilih (misalnya Senin)
                Booking::create([
                    'user_id' => $user->id,
                    'field_id' => $request->field_id,
                    'booking_date' => $bookingDate->format('Y-m-d'),
                    'start_time' => $bookingTime->format('H:i:s'),
                    'end_time' => $bookingTime->copy()->addHours(1)->format('H:i:s'),
                    'status' => 'pending',
                    'price' => $membership->price,
                    'order_id' => $transaction_details['order_id'],
                ]);
            }
        }

        // Redirect ke halaman pembayaran Midtrans
        return redirect()->route('midtrans.payment', ['snap_token' => $snapToken]);
    }

    public function payment(Request $request)
    {
        $snapToken = $request->snap_token;

        if (!$snapToken) {
            return back()->with('error', 'Token pembayaran tidak ditemukan.');
        }

        // Ambil data pembayaran berdasarkan snap_token
        $payment = Payment::where('payment_token', $snapToken)->first();
        if (!$payment) {
            return back()->with('error', 'Pembayaran tidak ditemukan.');
        }

        $payment->status = 'completed';

        // Update status booking menjadi 'completed'
        $orderId = $payment->order_id;
        $bookings = Booking::where('order_id', $orderId)->get();

        foreach ($bookings as $booking) {
            $cleanDate = Carbon::parse($booking->booking_date)->toDateString();
            $startTime = Carbon::parse($booking->start_time)->format('H:i');
            $endTime   = Carbon::parse($booking->end_time)->format('H:i');

            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $cleanDate . ' ' . $startTime);
            $endDateTime   = Carbon::createFromFormat('Y-m-d H:i', $cleanDate . ' ' . $endTime);

            // Memastikan hanya ada satu booking pada waktu yang dipilih
            // $existingBooking = Schedule::where('field_id', $booking->field_id)
            //     ->whereBetween('start_time', [$startDateTime])
            //     ->where('status', 'booked')
            //     ->exists();

            // if ($existingBooking) {
            //     return back()->with('error', 'Jam yang Anda pilih sudah dibooking.');
            // }

            // Lakukan pembaruan status untuk booking
            $booking->status = 'completed';
            $booking->save();

            // Perbarui status jadwal menjadi 'booked' jika belum ada booking
            $schedules = Schedule::where('field_id', $booking->field_id)
                ->where('start_time', $startDateTime)
                ->get();

            foreach ($schedules as $schedule) {
                $schedule->status = 'booked';
                $schedule->booked_by_user_id = $booking->user_id;
                $schedule->save();
            }
        }

        $payment->save();

        // Ambil nama membership
        $membershipName = Membership::join('user_memberships', 'memberships.id', '=', 'user_memberships.membership_id')
            ->where('user_memberships.order_id', $payment->order_id)
            ->value('memberships.name');

        return view('payment', [
            'snapToken' => $snapToken,
            'amount' => $payment->amount,
            'itemName' => $membershipName ?? 'Membership',
        ]);
    }

    public function notification(Request $request)
    {
        $notification = new Notification();

        // Ambil data status transaksi dari Midtrans
        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;

        $payment = Payment::where('order_id', $orderId)->first();
        $bookings = Booking::where('order_id', $orderId)->get();
        $user = $bookings->first() ? $bookings->first()->user : null;
        $field = $bookings->first() ? $bookings->first()->field : null;

        if ($transactionStatus == 'settlement') {
            // Pembayaran berhasil
            if ($payment) {
                $payment->status = 'completed';
                $payment->save();
            }

            // Update status booking menjadi 'completed'
            foreach ($bookings as $booking) {
                $booking->status = 'completed';
                $booking->save();
            }

            // Kirim notifikasi ke admin/owner menggunakan Filament
            $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
            foreach ($admins as $admin) {
                // Kirim notifikasi email
                $admin->notify(new PaymentStatusNotification('completed', $orderId, $payment->amount));
                foreach ($bookings as $booking) {
                    $admin->notify(new BookingStatusNotification('completed', $orderId, $field ? $field->name : '-', $booking->booking_date->format('d M Y'), $booking->start_time->format('H:i'), $booking->end_time->format('H:i')));
                }

                // Kirim notifikasi ke panel Filament
                FilamentNotification::make()
                    ->title('Pembayaran Membership Berhasil')
                    ->body('Order ID: ' . $orderId . ' - Rp ' . number_format($payment->amount, 0, ',', '.'))
                    ->success()
                    ->sendToDatabase($admin);

                FilamentNotification::make()
                    ->title('Membership Dikonfirmasi')
                    ->body('Order ID: ' . $orderId . ' - ' . count($bookings) . ' booking telah dikonfirmasi')
                    ->success()
                    ->sendToDatabase($admin);
            }
        } elseif ($transactionStatus == 'pending') {
            // Pembayaran masih pending
            if ($payment) {
                $payment->status = 'pending';
                $payment->save();
            }

            // Kirim notifikasi ke admin/owner menggunakan Filament
            $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
            foreach ($admins as $admin) {
                // Kirim notifikasi email
                $admin->notify(new PaymentStatusNotification('pending', $orderId, $payment->amount));
                foreach ($bookings as $booking) {
                    $admin->notify(new BookingStatusNotification('pending', $orderId, $field ? $field->name : '-', $booking->booking_date->format('d M Y'), $booking->start_time->format('H:i'), $booking->end_time->format('H:i')));
                }

                // Kirim notifikasi ke panel Filament
                FilamentNotification::make()
                    ->title('Pembayaran Membership Pending')
                    ->body('Order ID: ' . $orderId . ' - Rp ' . number_format($payment->amount, 0, ',', '.'))
                    ->warning()
                    ->sendToDatabase($admin);

                FilamentNotification::make()
                    ->title('Membership Pending')
                    ->body('Order ID: ' . $orderId . ' - ' . count($bookings) . ' booking menunggu pembayaran')
                    ->warning()
                    ->sendToDatabase($admin);
            }
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            // Pembayaran gagal
            if ($payment) {
                $payment->status = 'failed';
                $payment->save();
            }

            // Kirim notifikasi ke admin/owner menggunakan Filament
            $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
            foreach ($admins as $admin) {
                // Kirim notifikasi email
                $admin->notify(new PaymentStatusNotification('failed', $orderId, $payment->amount));
                foreach ($bookings as $booking) {
                    $admin->notify(new BookingStatusNotification('failed', $orderId, $field ? $field->name : '-', $booking->booking_date->format('d M Y'), $booking->start_time->format('H:i'), $booking->end_time->format('H:i')));
                }

                // Kirim notifikasi ke panel Filament
                FilamentNotification::make()
                    ->title('Pembayaran Membership Gagal')
                    ->body('Order ID: ' . $orderId . ' - Rp ' . number_format($payment->amount, 0, ',', '.'))
                    ->danger()
                    ->sendToDatabase($admin);

                FilamentNotification::make()
                    ->title('Membership Gagal')
                    ->body('Order ID: ' . $orderId . ' - ' . count($bookings) . ' booking dibatalkan')
                    ->danger()
                    ->sendToDatabase($admin);
            }
        }

        return response()->json('OK');
    }
}
