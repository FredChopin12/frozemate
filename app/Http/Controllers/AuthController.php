<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi tidak cocok.'])
                ->onlyInput('email');
        }

        // Cegah session fixation; isi keranjang tetap terbawa.
        $request->session()->regenerate();

        $fallback = $request->user()->isAdmin() ? route('admin.dashboard') : route('home');

        return redirect()->intended($fallback)
            ->with('success', 'Halo lagi, '.$request->user()->firstName().'!');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^(\+62|62|0)8[0-9\-\s]{7,14}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'phone.regex' => 'Nomor telepon harus nomor HP Indonesia, contoh: 0812-3456-7890.',
        ]);

        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Akun berhasil dibuat. Selamat datang di FrozeMate!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Kamu sudah keluar.');
    }
}
