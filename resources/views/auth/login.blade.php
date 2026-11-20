<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — SeaSupply Marketplace</title>

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
            <p class="ss-caption" style="margin-top: 4px;">Marketplace UMKM Olahan Laut</p>
        </div>

        {{-- Login Card --}}
        <div class="ss-surface-card" style="padding: var(--sp-6); box-shadow: var(--shadow-medium);">
            <h1 class="ss-h2" style="font-size: 20px; margin-bottom: 4px;">Selamat Datang</h1>
            <p class="ss-caption" style="margin-bottom: var(--sp-5);">Silakan masuk untuk mengelola toko atau berbelanja olahan laut.</p>

            {{-- Alert Error --}}
            @if($errors->any())
            <div class="ss-alert ss-alert--danger" role="alert" style="margin-bottom: var(--sp-4); font-size: 13px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('info'))
            <div class="ss-alert ss-alert--info" style="margin-bottom: var(--sp-4); font-size: 13px;">
                {{ session('info') }}
            </div>
            @endif

            {{-- 1-Click Demo Login Box (Mempermudah Pengujian Anda) --}}
            <div style="background: var(--color-seafoam-bg); border: 1.5px dashed var(--color-sea-teal); border-radius: var(--radius-sm); padding: var(--sp-4); margin-bottom: var(--sp-5);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--color-sea-teal);">
                        ⚡ 1-Click Demo Login
                    </span>
                    <span style="font-size: 10px; color: var(--color-muted);">Pilih Peran:</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    {{-- Demo Buyer --}}
                    <a href="{{ route('login.demo', 'buyer') }}" 
                       class="ss-btn ss-btn--outline ss-btn--sm" 
                       style="justify-content: flex-start; background: white; font-size: 12px; padding: 7px 10px;">
                        <span style="font-size: 14px;">🛒</span>
                        <div style="text-align: left; line-height: 1.2;">
                            <strong style="color: var(--color-ink);">Pembeli</strong>
                            <span class="ss-caption" style="display: block; font-size: 10px;">Budi Santoso &bull; Akses Katalog & Pesanan</span>
                        </div>
                    </a>

                    {{-- Demo Owner --}}
                    <a href="{{ route('login.demo', 'owner') }}" 
                       class="ss-btn ss-btn--outline ss-btn--sm" 
                       style="justify-content: flex-start; background: white; font-size: 12px; padding: 7px 10px;">
                        <span style="font-size: 14px;">👑</span>
                        <div style="text-align: left; line-height: 1.2;">
                            <strong style="color: var(--color-ink);">Pemilik Usaha (Owner)</strong>
                            <span class="ss-caption" style="display: block; font-size: 10px;">Pak Hasan &bull; Usaha Pak Hasan (Dashboard & Penjualan)</span>
                        </div>
                    </a>

                    {{-- Demo Employee --}}
                    <a href="{{ route('login.demo', 'employee') }}" 
                       class="ss-btn ss-btn--outline ss-btn--sm" 
                       style="justify-content: flex-start; background: white; font-size: 12px; padding: 7px 10px;">
                        <span style="font-size: 14px;">💼</span>
                        <div style="text-align: left; line-height: 1.2;">
                            <strong style="color: var(--color-ink);">Pegawai (Employee)</strong>
                            <span class="ss-caption" style="display: block; font-size: 10px;">Rian Hidayat &bull; Akses Terbatas Operasional</span>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Divider --}}
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: var(--sp-4);">
                <div style="flex: 1; height: 1px; background: var(--color-border);"></div>
                <span class="ss-caption" style="font-size: 11px;">atau masuk dengan email</span>
                <div style="flex: 1; height: 1px; background: var(--color-border);"></div>
            </div>

            {{-- Form Login Manual --}}
            <form action="{{ route('login.post') }}" method="POST" style="display: flex; flex-direction: column; gap: var(--sp-3);">
                @csrf

                <div class="ss-input-group">
                    <label class="ss-label" for="email">Alamat Email</label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           class="ss-input" 
                           placeholder="nama@email.com" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus>
                </div>

                <div class="ss-input-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="ss-label" for="password">Kata Sandi</label>
                    </div>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="ss-input" 
                           placeholder="••••••••" 
                           required>
                </div>

                {{-- Checkbox "Ingat saya" (Sesuai Design System Section 7) --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 2px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: var(--fs-body);">
                        <input type="checkbox" name="remember" style="accent-color: var(--color-sea-teal); width: 16px; height: 16px; border-radius: 4px;">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="ss-btn ss-btn--primary ss-btn--full" style="margin-top: var(--sp-2);">
                    Masuk ke SeaSupply
                </button>
            </form>

            {{-- Register Link --}}
            <div style="text-align: center; margin-top: var(--sp-4); padding-top: var(--sp-4); border-top: 1px solid var(--color-border);">
                <p class="ss-caption">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" style="color: var(--color-sea-teal); font-weight: 600; text-decoration: underline;">
                        Daftar sebagai Pembeli
                    </a>
                </p>
            </div>

        </div>

        {{-- Back to Catalog Link --}}
        <div style="text-align: center; margin-top: var(--sp-4);">
            <a href="{{ route('catalog.index') }}" style="font-size: var(--fs-caption); color: var(--color-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                &larr; Kembali ke Katalog Publik
            </a>
        </div>

    </div>
</div>

</body>
</html>
