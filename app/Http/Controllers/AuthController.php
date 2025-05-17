<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Halaman Login
    public function index()
    {
        return view('auth.login');
    }

    // Proses Login
    public function store(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        
        // Ambil pengguna yang sudah login
        $user = Auth::user();

        // Periksa peran pengguna
        if ($user->role === 'owner') {
            return redirect('/owner'); // Redirect ke dashboard owner
        } elseif ($user->role === 'admin') {
            return redirect('/admin'); // Redirect ke dashboard admin
        } else {
            return redirect('/'); // Redirect ke dashboard user biasa
        }
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ]);
}


    // Halaman Register
    public function create()
    {
        return view('auth.register');
    }

    // Proses Logout
    public function destroy($id = null)
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    // Proses Register (via /register)
    public function storeRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/')->with('success', 'Registrasi berhasil!');
    }
}
