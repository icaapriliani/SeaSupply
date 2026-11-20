<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun Pembeli — SeaSupply</title>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--color-surface); min-height: 100vh; display: flex; flex-direction: column;">

<div style="flex: 1; display: flex; align-items: center; justify-content: center; padding: var(--sp-6) var(--sp-4);">
    <div style="width: 100%; max-width: 440px;">

        {{-- Brand Logo --}}
        <div style="text-align: center; margin-bottom: var(--sp-5);">
            <a href="{{ route('catalog.index') }}" style="display: inline-flex; align-items: center; gap: 8px; font-size: 24px; font-weight: 700; color: var(--color-ocean-navy);">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-sm); background: linear-gradient(135deg, var(--color-sea-teal), var(--color-seafoam)); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    🌊
                </div>
                SeaSupply
            </a>
            <p class="ss-caption" style="margin-top: 4px;">Daftar Akun Pembeli Baru</p>
        </div>

        {{-- Register Card --}}
        <div class="ss-surface-card" style="padding: var(--sp-6); box-shadow: var(--shadow-medium);">
            <h1 class="ss-h2" style="font-size: 20px; margin-bottom: 4px;">Buat Akun Pembeli</h1>
            <p class="ss-caption" style="margin-bottom: var(--sp-5);">Daftar untuk mulai memesan aneka olahan laut langsung dari UMKM lokal.</p>

            @if($errors->any())
            <div class="ss-alert ss-alert--danger" role="alert" style="margin-bottom: var(--sp-4); font-size: 13px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $errors->first() }}
            </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" style="display: flex; flex-direction: column; gap: var(--sp-3);">
                @csrf

                <div class="ss-input-group">
                    <label class="ss-label" for="name">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="ss-input" placeholder="Budi Santoso" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="ss-input-group">
                    <label class="ss-label" for="email">Alamat Email</label>
                    <input type="email" name="email" id="email" class="ss-input" placeholder="budi@email.com" value="{{ old('email') }}" required>
                </div>

                <div class="ss-input-group">
                    <label class="ss-label" for="password">Kata Sandi (Min. 6 Karakter)</label>
                    <input type="password" name="password" id="password" class="ss-input" placeholder="••••••••" required>
                </div>

                <div class="ss-input-group">
                    <label class="ss-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="ss-input" placeholder="••••••••" required>
                </div>

                <button type="submit" class="ss-btn ss-btn--primary ss-btn--full" style="margin-top: var(--sp-3);">
                    Daftar Sekarang
                </button>
            </form>

            <div style="text-align: center; margin-top: var(--sp-4); padding-top: var(--sp-4); border-top: 1px solid var(--color-border);">
                <p class="ss-caption">
                    Sudah memiliki akun? 
                    <a href="{{ route('login') }}" style="color: var(--color-sea-teal); font-weight: 600; text-decoration: underline;">
                        Masuk di sini
                    </a>
                </p>
            </div>

        </div>

    </div>
</div>

</body>
</html>
