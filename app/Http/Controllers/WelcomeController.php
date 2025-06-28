<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Membership;
use App\Models\Schedule;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(Request $request): View
    {
        $fields = Field::orderBy('name')->get();
        $memberships = Membership::all();

        $notif = null;
        if ($request->has('order_id')) {
            $booking = Booking::where('order_id', $request->order_id)->first();
            if ($booking && $booking->status === 'completed') {
                $notif = "Booking pada " . $booking->booking_date->format('d M Y') .
                         " jam " . date('H:i', strtotime($booking->start_time)) .
                         " - " . date('H:i', strtotime($booking->end_time)) .
                         " berhasil!";
            }
        }
        if ($request->has('membership_order_id')) {
            // Membership sukses
            $notif = 'Berhasil menjadi member! Silakan cek jadwal anda.';
        }
        return view('welcome', compact('fields','memberships', 'notif'));
    }
}