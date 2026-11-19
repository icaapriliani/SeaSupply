<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SeaSupply') — Marketplace UMKM Olahan Laut</title>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body>

{{-- ── NAVIGATION — Buyer Topbar ───────────────────────────── --}}
<nav class="ss-nav" role="navigation" aria-label="Navigasi utama">
    <div class="ss-nav__inner">

        {{-- Brand --}}
        <a href="{{ route('catalog.index') }}" class="ss-nav__brand" aria-label="SeaSupply - Halaman Utama">
            <div class="ss-nav__brand-icon" aria-hidden="true">🌊</div>
            SeaSupply
        </a>

        {{-- Search --}}
        <form class="ss-nav__search" action="{{ route('catalog.index') }}" method="GET" role="search">
            <span class="ss-nav__search-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
            </span>
            @if(request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari rajungan, ikan kering..."
                aria-label="Cari produk olahan laut"
                autocomplete="off"
            >
        </form>

        {{-- Nav Links --}}
        <ul class="ss-nav__links" role="list">
            <li>
                <a href="{{ route('catalog.index') }}"
                   class="ss-nav__link {{ request()->routeIs('catalog.index') && !request()->routeIs('catalog.products') ? 'active' : '' }}"
                   aria-label="Beranda">
                    <span class="ss-nav__link-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </span>
                    <span>Beranda</span>
                </a>
            </li>
            <li>
                <a href="{{ route('catalog.index') }}"
                   class="ss-nav__link"
                   aria-label="Produk">
                    <span class="ss-nav__link-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                    </span>
                    <span>Produk</span>
                </a>
            </li>
            <li>
                <a href="#" class="ss-nav__link" aria-label="Pesanan saya">
                    <span class="ss-nav__link-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </span>
                    <span>Pesanan</span>
                </a>
            </li>
            <li>
                <a href="#" class="ss-nav__link" aria-label="Favorit">
                    <span class="ss-nav__link-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </span>
                    <span>Favorit</span>
                </a>
            </li>
            <li>
                <a href="#" class="ss-nav__link" aria-label="Akun saya">
                    <span class="ss-nav__link-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <span>Akun</span>
                </a>
            </li>
        </ul>

    </div>
</nav>

{{-- ── MAIN CONTENT ─────────────────────────────────────────── --}}
<main id="main-content" role="main">
    @yield('content')
</main>

{{-- ── FOOTER ───────────────────────────────────────────────── --}}
<footer class="ss-footer" role="contentinfo">
    <div class="ss-footer__inner">
        <div class="ss-footer__brand">🌊 SeaSupply</div>
        <div class="ss-footer__copy">© {{ date('Y') }} SeaSupply — Marketplace UMKM Olahan Laut Indonesia</div>
    </div>
</footer>

{{-- ── TOAST CONTAINER ─────────────────────────────────────── --}}
<div class="ss-toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

@stack('scripts')
</body>
</html>
