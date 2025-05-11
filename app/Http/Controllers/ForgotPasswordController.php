<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    // Menampilkan form lupa password
    public function showLinkRequestForm()
    {
        return view('forgot_password');
    }

    // Mengirim email reset password
    public function sendResetLinkEmail(Request $request)
    {
        // Validasi email
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Mengirim link reset password
        $response = Password::sendResetLink($request->only('email'));

        // Memberikan feedback ke pengguna
        if ($response == Password::RESET_LINK_SENT) {
            return back()->with('status', 'Link reset password telah dikirim ke email Anda!');
        } else {
            return back()->withErrors(['email' => 'Terjadi kesalahan, coba lagi nanti.']);
        }
    }
}
