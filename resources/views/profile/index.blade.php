@extends('layouts.buyer')

@section('title', 'Profil Akun Saya')

@section('content')
<div class="ss-page">

    {{-- ── BREADCRUMB ───────────────────────────────────── --}}
    <nav aria-label="Breadcrumb" style="margin-bottom:var(--sp-4);">
        <ol style="display:flex;align-items:center;gap:8px;list-style:none;font-size:var(--fs-label);color:var(--color-muted);">
            <li><a href="{{ route('catalog.index') }}" style="color:var(--color-sea-teal);font-weight:500;">Beranda</a></li>
            <li aria-hidden="true">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </li>
            <li aria-current="page" style="color:var(--color-ink);font-weight:600;">Profil Akun</li>
        </ol>
    </nav>

    {{-- ── NOTIFICATION SUCCESS ─────────────────────────── --}}
    @if(session('success'))
    <div class="ss-alert ss-alert--success" role="alert" style="margin-bottom:var(--sp-5);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        <div>
            <strong>Berhasil!</strong> {{ session('success') }}
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="ss-alert ss-alert--danger" role="alert" style="margin-bottom:var(--sp-5);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div>
            <strong>Periksa kembali data Anda:</strong>
            <ul style="margin:4px 0 0 16px;font-size:var(--fs-label);">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ── PROFILE CARD CONTAINER ───────────────────────── --}}
    <div class="ss-surface-card" style="max-width:680px;margin:0 auto;padding:var(--sp-6);">

        {{-- Header Profil --}}
        <div style="display:flex;align-items:center;gap:var(--sp-4);padding-bottom:var(--sp-5);border-bottom:1px solid var(--color-border);margin-bottom:var(--sp-5);">
            <div style="
                width:64px;height:64px;
                background:linear-gradient(135deg, var(--color-ocean-navy), var(--color-sea-teal));
                color:var(--color-white);
                border-radius:var(--radius-full);
                display:flex;align-items:center;justify-content:center;
                font-size:26px;font-weight:var(--fw-bold);
                box-shadow:var(--shadow-low);
                flex-shrink:0;
            ">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div style="flex:1;">
                <h1 class="ss-h2" style="margin-bottom:2px;">{{ $user->name }}</h1>
                <div style="font-size:var(--fs-label);color:var(--color-muted);display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span>{{ $user->email }}</span>
                    <span>&bull;</span>
                    @php
                        $role = $user->memberships->first()?->role;
                    @endphp
                    <span class="ss-badge ss-badge--available" style="font-size:10px;padding:2px 8px;">
                        {{ $role === 'owner' ? '👑 Pemilik Toko' : ($role === 'employee' ? '💼 Pegawai Toko' : '🛒 Akun Pembeli') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form Edit Profil --}}
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div style="display:flex;flex-direction:column;gap:var(--sp-4);">

                {{-- Nama Lengkap --}}
                <div class="ss-input-group">
                    <label for="name" class="ss-label" style="display:flex;justify-content:space-between;">
                        <span>Nama Lengkap <span style="color:var(--color-danger);">&ast;</span></span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        class="ss-input @error('name') error @enderror"
                        placeholder="Contoh: Budi Santoso"
                        required
                        autocomplete="name"
                    >
                    @error('name')
                        <span style="font-size:var(--fs-caption);color:var(--color-danger);">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Alamat Email --}}
                <div class="ss-input-group">
                    <label for="email" class="ss-label" style="display:flex;justify-content:space-between;">
                        <span>Alamat Email <span style="color:var(--color-danger);">&ast;</span></span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        class="ss-input @error('email') error @enderror"
                        placeholder="nama@email.com"
                        required
                        autocomplete="email"
                    >
                    @error('email')
                        <span style="font-size:var(--fs-caption);color:var(--color-danger);">{{ $message }}</span>
                    @enderror
                    <span class="ss-caption">Email digunakan untuk notifikasi akun dan autentikasi.</span>
                </div>

                {{-- Nomor WhatsApp / Telepon --}}
                <div class="ss-input-group">
                    <label for="phone" class="ss-label">
                        <span>Nomor WhatsApp / Telepon</span>
                    </label>
                    <div style="position:relative;">
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $user->phone ?? '') }}"
                            class="ss-input @error('phone') error @enderror"
                            placeholder="Contoh: 0812-3456-7890"
                            autocomplete="tel"
                        >
                    </div>
                    @error('phone')
                        <span style="font-size:var(--fs-caption);color:var(--color-danger);">{{ $message }}</span>
                    @enderror
                    <span class="ss-caption">Nomor telepon akan otomatis mengisi formulir konfirmasi pesanan olahan laut Anda.</span>
                </div>

                {{-- Action Buttons --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--sp-3);padding-top:var(--sp-4);border-top:1px solid var(--color-border);margin-top:var(--sp-2);">
                    <a href="{{ route('catalog.index') }}" class="ss-btn ss-btn--outline" style="min-width:110px;">
                        Kembali
                    </a>

                    <button type="submit" class="ss-btn ss-btn--primary" style="min-width:160px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>

            </div>
        </form>

    </div>

</div>
@endsection
