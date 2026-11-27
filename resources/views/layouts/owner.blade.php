<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Pemilik Usaha') — SeaSupply</title>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body style="background-color: var(--color-surface);">

<div class="ss-sidebar-mobile-bar">
    <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:15px;">
        <span>🌊</span>
        <span>SeaSupply Owner</span>
    </div>
    <button type="button" class="ss-sidebar-toggle" id="ownerSidebarToggle" aria-label="Buka menu navigasi">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
        Menu
    </button>
</div>
<div class="ss-sidebar-backdrop" id="ownerSidebarBackdrop"></div>

<div class="ss-sidebar-layout">

    {{-- ── SIDEBAR OWNER (Ocean Navy) ──────────────────────── --}}
    <aside class="ss-sidebar" id="ownerSidebar" aria-label="Navigasi Pemilik Usaha">
        
        {{-- Brand --}}
        <div class="ss-sidebar__brand">
            <div class="ss-sidebar__brand-icon" aria-hidden="true">🌊</div>
            <div>
                <div style="font-size:16px;line-height:1.2;">SeaSupply</div>
                <div style="font-size:10px;font-weight:400;color:var(--color-seafoam);opacity:0.85;">Panel Pemilik Usaha</div>
            </div>
        </div>

        {{-- Store Info Chip --}}
        <div style="padding:0 var(--sp-4) var(--sp-4);margin-bottom:var(--sp-2);">
            <div style="background:rgba(255,255,255,.08);border-radius:var(--radius-sm);padding:8px 12px;border:1px solid rgba(255,255,255,.12);">
                <div style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--color-seafoam);font-weight:600;">Toko Anda</div>
                <div style="font-size:13px;font-weight:600;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Usaha Pak Hasan</div>
                <div style="font-size:11px;color:rgba(255,255,255,.6);">Cirebon, Jawa Barat</div>
            </div>
        </div>

        {{-- Navigation Menu (Wireframe C & Design System) --}}
        <nav class="ss-sidebar__nav" role="navigation">
            <a href="{{ route('owner.dashboard') }}" 
               class="ss-sidebar__link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                <span class="ss-sidebar__link-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                </span>
                Dashboard
            </a>

            <a href="{{ route('owner.products') }}" 
               class="ss-sidebar__link {{ request()->routeIs('owner.products*') ? 'active' : '' }}">
                <span class="ss-sidebar__link-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </span>
                Produk
            </a>

            <a href="{{ route('owner.orders') }}" 
               class="ss-sidebar__link {{ request()->routeIs('owner.orders*') ? 'active' : '' }}">
                <span class="ss-sidebar__link-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </span>
                Pesanan
            </a>

            <a href="{{ route('owner.finance') }}" 
               class="ss-sidebar__link {{ request()->routeIs('owner.finance*') ? 'active' : '' }}">
                <span class="ss-sidebar__link-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </span>
                Keuangan
            </a>

            <a href="{{ route('owner.employees') }}" 
               class="ss-sidebar__link {{ request()->routeIs('owner.employees*') ? 'active' : '' }}">
                <span class="ss-sidebar__link-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
                Pegawai
            </a>
        </nav>

        {{-- Switch to Buyer View / Logout --}}
        <div style="padding:var(--sp-4);border-top:1px solid rgba(255,255,255,.1);display:flex;flex-direction:column;gap:8px;">
            <a href="{{ route('catalog.index') }}" 
               style="font-size:var(--fs-caption);color:var(--color-seafoam);display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Buka Katalog Pembeli
            </a>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;font-size:var(--fs-caption);color:#ffb3b3;display:flex;align-items:center;gap:6px;padding:0;" title="Keluar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Keluar Akun
                </button>
            </form>
        </div>

    </aside>

    {{-- ── MAIN CONTENT AREA ──────────────────────────────── --}}
    <div class="ss-sidebar__content">
        
        {{-- Top Bar in Content --}}
        <header class="ss-sidebar-header">
            <div>
                <h1 class="ss-h2" style="margin-bottom:2px;">@yield('header-title', 'Dashboard pemilik usaha')</h1>
                <p class="ss-caption">Kelola produk olahan laut, pesanan masuk, dan operasional usaha Anda.</p>
            </div>
            
            <div style="display:flex;align-items:center;gap:var(--sp-3);">
                <div style="text-align:right;">
                    <div style="font-size:var(--fs-label);font-weight:600;color:var(--color-ink);">Pak Hasan</div>
                    <div style="font-size:var(--fs-caption);color:var(--color-sea-teal);font-weight:500;">Pemilik Usaha (Owner)</div>
                </div>
                <div style="width:38px;height:38px;border-radius:var(--radius-full);background:var(--color-ocean-navy);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">
                    PH
                </div>
            </div>
        </header>

        {{-- Dynamic Content --}}
        <main>
            @yield('content')
        </main>

    </div>

</div>

{{-- TOAST NOTIFICATION CONTAINER --}}
<div class="ss-toast-container" id="toastContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('ownerSidebarToggle');
    const sidebar = document.getElementById('ownerSidebar');
    const backdrop = document.getElementById('ownerSidebarBackdrop');
    if (toggle && sidebar && backdrop) {
        const closeSidebar = () => {
            sidebar.classList.remove('open');
            backdrop.classList.remove('open');
        };
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            backdrop.classList.toggle('open');
        });
        backdrop.addEventListener('click', closeSidebar);
    }
});
</script>
@stack('scripts')
</body>
</html>
