<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordController extends Controller
{
    // Menampilkan form reset password
    public function showResetForm($token)
    {
        return view('reset_password', ['token' => $token]);
    }

    // Mengupdate password pengguna
    public function reset(Request $request)
    {
        // Validasi input password baru
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required',
        ]);

        // Melakukan reset password
        $response = Password::reset(
            $validated,
            function ($user) use ($validated) {
                $user->password = Hash::make($validated['password']);
                $user->save();
            }
        );

        // Feedback berdasarkan status reset password
        if ($response == Password::PASSWORD_RESET) {
            return redirect()->route('welcome')->with('status', 'Password berhasil diubah!');
        } else {
            return back()->withErrors(['email' => 'Link reset password tidak valid.']);
        }
    }
}
