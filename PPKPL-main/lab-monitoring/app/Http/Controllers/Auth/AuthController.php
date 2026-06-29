<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     * Jika sudah login, redirect langsung ke monitoring.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('monitoring');
        }

        return view('auth.login');
    }

    /**
     * Proses login.
     * Validasi kredensial, regenerasi session, redirect ke halaman tujuan.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Attempt login dengan remember me opsional
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Regenerasi session ID untuk mencegah session fixation attack
            $request->session()->regenerate();

            // Redirect ke halaman yang dituju sebelumnya, atau ke monitoring
            return redirect()->intended(route('monitoring'));
        }

        // Login gagal — kembalikan ke form dengan pesan error
        return back()
            ->withErrors(['email' => 'Email atau password tidak valid.'])
            ->onlyInput('email');
    }

    /**
     * Proses logout.
     * Hapus session dan token CSRF, redirect ke halaman login.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate seluruh session agar tidak bisa digunakan kembali
        $request->session()->invalidate();

        // Regenerasi CSRF token setelah logout
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
