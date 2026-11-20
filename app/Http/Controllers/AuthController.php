<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Store;
use App\Models\StoreMembership;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Proses login manual (Email & Password)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan tidak cocok.',
        ])->onlyInput('email');
    }

    /**
     * 1-Click Demo Login untuk kemudahan pengujian
     */
    public function demoLogin(string $role)
    {
        $emailMap = [
            'owner'    => 'hasan@seasupply.local',
            'employee' => 'rian@seasupply.local',
            'buyer'    => 'budi@seasupply.local',
        ];

        $targetEmail = $emailMap[$role] ?? 'budi@seasupply.local';

        $user = User::where('email', $targetEmail)->first();

        if (!$user) {
            // Jika user belum ada, buat otomatis
            $nameMap = [
                'owner'    => 'Pak Hasan',
                'employee' => 'Rian Hidayat',
                'buyer'    => 'Budi Santoso',
            ];

            $user = User::create([
                'name'     => $nameMap[$role] ?? 'Pengguna',
                'email'    => $targetEmail,
                'password' => Hash::make('password123'),
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Tampilkan halaman register pembeli
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('catalog.index');
        }

        return view('auth.register');
    }

    /**
     * Proses register pembeli baru
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('catalog.index')->with('success', 'Akun berhasil didaftarkan! Selamat datang di SeaSupply.');
    }

    /**
     * Proses logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }

    /**
     * Helper redirect sesuai peran user
     */
    private function redirectBasedOnRole(User $user)
    {
        $membership = StoreMembership::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($membership) {
            if ($membership->role === 'owner') {
                return redirect()->intended(route('owner.dashboard'));
            } elseif ($membership->role === 'employee') {
                return redirect()->intended(route('employee.orders'));
            }
        }

        return redirect()->intended(route('catalog.index'));
    }
}
