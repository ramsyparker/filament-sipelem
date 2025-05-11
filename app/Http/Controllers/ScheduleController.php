<?php
// app/Http/Controllers/ScheduleController.php
namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        // Ambil jadwal yang sudah dipesan oleh user yang sedang login
        $schedules = Schedule::where('booked_by_user_id', Auth::id())
                             ->with('field') // Mengambil relasi field
                             ->get();

        // Cek apakah ada jadwal yang dipesan
        if ($schedules->isEmpty()) {
            // Jika belum ada jadwal yang dipesan, tampilkan pesan
            return view('schedule.index', ['message' => 'Anda belum memiliki jadwal, silakan booking terlebih dahulu.']);
        }

        // Jika ada jadwal, tampilkan data jadwal yang sudah dipesan
        return view('schedule.index', compact('schedules'));
    }
}
