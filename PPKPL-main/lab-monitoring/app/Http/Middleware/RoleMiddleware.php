<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Periksa apakah user yang sedang login memiliki role yang diizinkan.
     *
     * Penggunaan pada route:
     *   ->middleware('role:admin')          — hanya admin
     *   ->middleware('role:admin,petugas')  — admin atau petugas
     *
     * @param  string  ...$roles  Daftar role yang diizinkan (dari parameter middleware)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Pastikan user sudah login (double-check, biasanya sudah dijaga auth middleware)
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user     = Auth::user();
        $userRole = $user->role;

        // Cek apakah role user ada dalam daftar role yang diizinkan
        if (! in_array($userRole, $roles)) {
            // Jika request AJAX/JSON, kembalikan response JSON 403
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak. Anda tidak memiliki hak akses ke halaman ini.',
                ], 403);
            }

            // Untuk request web biasa, abort dengan 403
            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}