@extends('layouts.owner')

@section('title', 'Kelola Pegawai')
@section('header-title', 'Pengelolaan pegawai & hak akses')

@section('content')
<section class="ss-surface-card" style="margin-bottom:var(--sp-6);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);">
        <div>
            <h2 class="ss-h3" style="margin-bottom:2px;">Daftar Pegawai Toko</h2>
            <p class="ss-caption">Atur hak akses operasional pegawai sesuai kebutuhan toko.</p>
        </div>
        <button class="ss-btn ss-btn--navy ss-btn--sm" id="btnTambahPegawai">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Pegawai
        </button>
    </div>

    <div class="ss-table-wrapper">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Nama & Email</th>
                    <th>Peran</th>
                    <th>Hak Akses (Permissions)</th>
                    <th>Status</th>
                    <th style="text-align:right;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $emp)
                <tr>
                    <td>
                        <strong style="color:var(--color-ink);">{{ $emp['name'] }}</strong>
                        <div class="ss-caption">{{ $emp['email'] }}</div>
                    </td>
                    <td>{{ $emp['role'] }}</td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            @foreach($emp['permissions'] as $p)
                                <span class="ss-badge ss-badge--processing" style="font-size:10px;">{{ $p }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <span class="ss-badge ss-badge--completed">{{ $emp['status'] }}</span>
                    </td>
                    <td style="text-align:right;">
                        <button class="ss-btn ss-btn--outline ss-btn--sm" onclick="alert('Form ubah izin pegawai akan aktif pada tahap backend RBAC.')">
                            Atur Izin
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:var(--sp-4);">
        <a href="{{ route('owner.dashboard') }}" class="ss-btn ss-btn--outline ss-btn--sm">
            &larr; Kembali ke Dashboard
        </a>
    </div>
</section>
@endsection
