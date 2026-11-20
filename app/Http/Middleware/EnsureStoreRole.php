<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StoreMembership;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
        }

        $user = Auth::user();

        $membership = StoreMembership::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($role === 'owner') {
            if (!$membership || $membership->role !== 'owner') {
                abort(403, 'Akses Ditolak: Halaman ini hanya dapat diakses oleh Pemilik Usaha (Owner).');
            }
        } elseif ($role === 'employee') {
            if (!$membership || !in_array($membership->role, ['employee', 'owner'], true)) {
                abort(403, 'Akses Ditolak: Anda belum terdaftar sebagai staf pegawai toko aktif.');
            }
        }

        return $next($request);
    }
}
