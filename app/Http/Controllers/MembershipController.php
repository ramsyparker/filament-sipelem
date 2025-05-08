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

        // Membuat keanggotaan pengguna dan booking
        $userMembership = UserMembership::create([
            'user_id' => $user->id,
            'membership_id' => $membership->id,
            'field_id' => $request->field_id,
            'booking_time' => $bookingTime,
            'day_of_week' => $request->day_of_week,
            'start_date' => now(),
            'end_date' => now()->addDays($membership->duration),
            'status' => 'active',
            'order_id' => $transaction_details['order_id'], // Menyimpan order_id yang unik
        ]);

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

        // Loop untuk membuat pemesanan setiap minggu selama durasi membership
        for ($i = 0; $i < $weeksToBook; $i++) {
            $bookingDate = $firstBookingDay->copy()->addDays(7 * $i);  // Menggunakan addDays untuk interval 7 hari

            // Membuat entri pemesanan untuk setiap minggu
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

        // Mengupdate status booking menjadi 'pending' setelah pemesanan dibuat
        Booking::where('order_id', $transaction_details['order_id'])
            ->update(['status' => 'pending']);

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
        $payment->status = 'completed';

        // Update status booking menjadi 'completed'
        $orderId = $payment->order_id; // Menambahkan baris untuk mendefinisikan $orderId
        $bookings = Booking::where('order_id', $orderId)->get();

        foreach ($bookings as $booking) {
            $cleanDate = Carbon::parse($booking->booking_date)->toDateString();
            $startTime = Carbon::parse($booking->start_time)->format('H:i');
            $endTime   = Carbon::parse($booking->end_time)->format('H:i');

            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $cleanDate . ' ' . $startTime);
            $endDateTime   = Carbon::createFromFormat('Y-m-d H:i', $cleanDate . ' ' . $endTime);

            $schedules = Schedule::where('field_id', $booking->field_id)
                ->whereBetween('start_time', [$startDateTime, $endDateTime->subSecond()])
                ->get();

            foreach ($schedules as $schedule) {
                $schedule->status = 'booked';
                $schedule->booked_by_user_id = $booking->user_id;
                $schedule->save();
            }

            $booking->status = 'completed';
            $booking->save();
        }

        $field = $booking->field; // Asumsikan ada relasi antara booking dan field
        $cleanDate = Carbon::parse($booking->booking_date)->toDateString(); // contoh hasil: '2025-05-08'

        // Ambil hanya jam dan menit dari start_time dan end_time (format: H:i)
        $cleanStartTime = Carbon::parse($booking->start_time)->format('H:i');
        $cleanEndTime   = Carbon::parse($booking->end_time)->format('H:i');
        // Gabungkan booking_date dan start_time menjadi start_time lengkap (datetime)
        $startString = $cleanDate . ' ' . $cleanStartTime; // Menggabungkan tanggal dan waktu mulai
        $endString = $cleanDate . ' ' . $cleanEndTime; // Menggabungkan tanggal dan waktu selesai
        try {
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $startString);
            $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $endString);
            // Parsing end time
        } catch (\Exception $e) {
            return back();
        }


        $schedules = Schedule::where('field_id', $booking->field_id)
            ->whereBetween('start_time', [$startDateTime, $endDateTime->subSecond()])
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
            $booking->status = 'completed'; // Update status booking
            $schedule->save();
        }
        $booking->status = 'completed';
        $booking->save();

        if (!$payment) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $payment->status = 'completed';
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
        // Inisialisasi notifikasi dari Midtrans
        $notification = new Notification();

        // Ambil data status transaksi dari Midtrans
        $transactionStatus = $notification->transaction_status;  // Mengakses property transaction_status
        $orderId = $notification->order_id;  // Mengakses order_id dari notifikasi

        // Cek status transaksi
        if ($transactionStatus == 'settlement') {
            // Pembayaran berhasil
            // Update status pembayaran dan booking
            $payment = Payment::where('order_id', $orderId)->first();
            $payment->status = 'completed';
            $payment->save();

            // Update status booking menjadi 'completed'
            Booking::where('order_id', $orderId)->update(['status' => 'completed']);
        } elseif ($transactionStatus == 'pending') {
            // Pembayaran masih pending
            $payment = Payment::where('order_id', $orderId)->first();
            $payment->status = 'pending';
            $payment->save();
        } elseif ($transactionStatus == 'failed') {
            // Pembayaran gagal
            $payment = Payment::where('order_id', $orderId)->first();
            $payment->status = 'failed';
            $payment->save();
        }

        return response()->json('OK'); // Response 'OK' untuk Midtrans
    }
}
