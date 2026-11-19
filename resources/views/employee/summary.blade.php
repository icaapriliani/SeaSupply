@extends('layouts.employee')

@section('title', 'Ringkasan Operasional')
@section('header-title', 'Ringkasan tugas operasional')

@section('content')
<div class="ss-surface-card" style="margin-bottom:var(--sp-5);background:var(--color-surface);border:1.5px solid var(--color-border);padding:var(--sp-4);">
    <div style="display:flex;align-items:flex-start;gap:var(--sp-3);">
        <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:var(--color-info-bg);color:var(--color-info);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
            ℹ️
        </div>
        <div>
            <h2 class="ss-h3" style="font-size:15px;margin-bottom:2px;color:var(--color-ink);">Ringkasan Harian Pegawai</h2>
            <p class="ss-caption" style="color:var(--color-muted);line-height:1.5;">
                Berikut rangkuman aktivitas operasional yang dapat Anda tangani hari ini.
            </p>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:var(--sp-4);margin-bottom:var(--sp-6);">
    <div class="ss-stat-card">
        <span class="ss-stat-card__label">Pesanan Perlu Diproses</span>
        <div class="ss-stat-card__value" style="color:var(--color-warning);">{{ $pendingCount }}</div>
        <div class="ss-stat-card__sub">Menunggu konfirmasi packing</div>
    </div>
    <div class="ss-stat-card">
        <span class="ss-stat-card__label">Pesanan Sedang Dipacking</span>
        <div class="ss-stat-card__value" style="color:var(--color-info);">{{ $processingCount }}</div>
        <div class="ss-stat-card__sub">Siap dikirim ke kurir/pembeli</div>
    </div>
    <div class="ss-stat-card">
        <span class="ss-stat-card__label">Produk Perlu Restock</span>
        <div class="ss-stat-card__value" style="color:var(--color-danger);">{{ $lowStockCount }}</div>
        <div class="ss-stat-card__sub">Stok di bawah 10 unit / habis</div>
    </div>
</div>

<div style="margin-top:var(--sp-4);">
    <a href="{{ route('employee.orders') }}" class="ss-btn ss-btn--primary">
        Buka Daftar Pesanan Masuk &rarr;
    </a>
</div>
@endsection
