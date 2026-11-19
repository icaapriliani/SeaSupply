<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tugas Operasional') — SeaSupply Pegawai</title>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body style="background-color: var(--color-surface);">

<div class="ss-sidebar-layout">

    {{-- ── SIDEBAR EMPLOYEE (Akses Terbatas — Design System Muted Slate) ── --}}
    <aside class="ss-sidebar" style="background:#25343A;" aria-label="Navigasi Pegawai Operasional">
        
        {{-- Brand --}}
        <div class="ss-sidebar__brand">
            <div class="ss-sidebar__brand-icon" style="background:rgba(131,197,190,.2);" aria-hidden="true">🌊</div>
            <div>
                <div style="font-size:16px;line-height:1.2;">SeaSupply</div>
                <div style="font-size:10px;font-weight:500;color:var(--color-seafoam);">Operasional Pegawai</div>
            </div>
        </div>

        {{-- Store & Role Info --}}
        <div style="padding:0 var(--sp-4) var(--sp-3);">
            <div style="background:rgba(255,255,255,.06);border-radius:var(--radius-sm);padding:8px 12px;border:1px solid rgba(255,255,255,.08);">
                <div style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--color-seafoam);font-weight:600;">Toko</div>
                <div style="font-size:13px;font-weight:600;color:white;">Usaha Pak Hasan</div>
                <div style="display:flex;align-items:center;gap:4px;margin-top:4px;">
                    <span class="ss-badge" style="background:rgba(131,197,190,.15);color:var(--color-seafoam);font-size:10px;padding:1px 6px;">
                        Akses Terbatas
                    </span>
                </div>
            </div>
        </div>

        {{-- Menu Sidebar Terbatas (Sesuai Wireframe D) --}}
        <nav class="ss-sidebar__nav" role="navigation">
            <a href="{{ route('employee.summary') }}" 
               class="ss-sidebar__link {{ request()->routeIs('employee.summary') ? 'active' : '' }}">
                <span class="ss-sidebar__link-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                </span>
                Ringkasan
            </a>

            <a href="{{ route('employee.orders') }}" 
               class="ss-sidebar__link {{ request()->routeIs('employee.orders*') ? 'active' : '' }}">
                <span class="ss-sidebar__link-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </span>
                Pesanan
            </a>

            <a href="{{ route('employee.products') }}" 
               class="ss-sidebar__link {{ request()->routeIs('employee.products*') ? 'active' : '' }}">
                <span class="ss-sidebar__link-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </span>
                Produk
            </a>
        </nav>

        {{-- Switch View / Logout --}}
        <div style="padding:var(--sp-4);border-top:1px solid rgba(255,255,255,.08);display:flex;flex-direction:column;gap:8px;">
            <a href="{{ route('owner.dashboard') }}" 
               style="font-size:var(--fs-caption);color:rgba(255,255,255,.6);display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Beralih ke Panel Owner
            </a>
            <a href="{{ route('catalog.index') }}" 
               style="font-size:var(--fs-caption);color:var(--color-seafoam);display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path d="m10 15 5-3-5-3v6z"/>
                </svg>
                Buka Katalog Pembeli
            </a>
        </div>

    </aside>

    {{-- ── MAIN CONTENT AREA ──────────────────────────────── --}}
    <div class="ss-sidebar__content">
        
        {{-- Top Bar in Content --}}
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);padding-bottom:var(--sp-3);border-bottom:1px solid var(--color-border);">
            <div>
                <h1 class="ss-h2" style="margin-bottom:2px;">@yield('header-title', 'Tugas operasional')</h1>
                <p class="ss-caption">Panel penanganan operasional pesanan dan pengecekan stok produk.</p>
            </div>
            
            <div style="display:flex;align-items:center;gap:var(--sp-3);">
                <div style="text-align:right;">
                    <div style="font-size:var(--fs-label);font-weight:600;color:var(--color-ink);">Rian Hidayat</div>
                    <div style="font-size:var(--fs-caption);color:var(--color-muted);font-weight:500;">Pegawai (Employee)</div>
                </div>
                <div style="width:38px;height:38px;border-radius:var(--radius-full);background:#3A4D55;color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">
                    RH
                </div>
            </div>
        </header>

        {{-- Dynamic Content --}}
        <main>
            @yield('content')
        </main>

        {{-- Footer Note Sesuai Wireframe D --}}
        <div style="margin-top:var(--sp-7);padding-top:var(--sp-4);border-top:1px dashed var(--color-border);text-align:center;">
            <p class="ss-caption" style="color:var(--color-muted);">
                🔒 <strong>Keterangan:</strong> Pegawai tidak dapat mengakses pengaturan toko atau laporan sensitif (keuangan/penjualan & manajemen staf).
            </p>
        </div>

    </div>

</div>

{{-- TOAST NOTIFICATION CONTAINER --}}
<div class="ss-toast-container" id="toastContainer"></div>

@stack('scripts')
</body>
</html>
