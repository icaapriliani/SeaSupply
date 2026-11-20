@extends('layouts.owner')

@section('title', 'Laporan Keuangan & Penjualan')
@section('header-title', 'Laporan Keuangan & Penjualan Toko')

@section('content')

{{-- ── FILTER PERIODE PENJUALAN (Sesuai Design System Sales Card) ──── --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-3);">
    <div>
        <h2 class="ss-h3" style="margin-bottom:2px;">Analisis Performa Penjualan</h2>
        <p class="ss-caption">Pantau pendapatan, volume produk terjual, dan riwayat transaksi usaha Anda.</p>
    </div>

    {{-- Filter Periode --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <a href="{{ route('owner.finance', ['period' => 'all']) }}" 
           class="ss-chip {{ !request('period') || request('period') === 'all' ? 'active' : '' }}" style="padding:5px 12px;font-size:11px;">
            Sepanjang Waktu
        </a>
        <a href="{{ route('owner.finance', ['period' => 'month']) }}" 
           class="ss-chip {{ request('period') === 'month' ? 'active' : '' }}" style="padding:5px 12px;font-size:11px;">
            Bulan Ini
        </a>
        <a href="{{ route('owner.finance', ['period' => 'week']) }}" 
           class="ss-chip {{ request('period') === 'week' ? 'active' : '' }}" style="padding:5px 12px;font-size:11px;">
            7 Hari Terakhir
        </a>
        <a href="{{ route('owner.finance', ['period' => 'today']) }}" 
           class="ss-chip {{ request('period') === 'today' ? 'active' : '' }}" style="padding:5px 12px;font-size:11px;">
            Hari Ini
        </a>
    </div>
</div>

{{-- ── 1. KARTU METRIK KEUANGAN (Design System Sales Card & Stats) ──── --}}
<section aria-label="Ringkasan Finansial" style="margin-bottom: var(--sp-6);">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:var(--sp-4);">
        
        {{-- Sales Card (Design System Section 9) --}}
        <div class="ss-stat-card" style="border-top:3px solid var(--color-sea-teal);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <span class="ss-stat-card__label">Total Penjualan</span>
                <span style="font-size:18px;">💰</span>
            </div>
            <div class="ss-stat-card__value" style="color:var(--color-ocean-navy);">
                Rp {{ number_format($metrics['total_sales'], 0, ',', '.') }}
            </div>
            <div class="ss-stat-card__sub" style="color:var(--color-sea-teal);font-weight:500;">
                Periode yang dipilih: {{ $periodLabel }}
            </div>
        </div>

        {{-- Card 2: Pesanan Berhasil --}}
        <div class="ss-stat-card" style="border-top:3px solid var(--color-success);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <span class="ss-stat-card__label">Pesanan Berhasil / Lunas</span>
                <span style="font-size:18px;">✅</span>
            </div>
            <div class="ss-stat-card__value">{{ $metrics['paid_orders_count'] }}</div>
            <div class="ss-stat-card__sub" style="color:var(--color-success);">
                Dari total {{ $metrics['all_orders_count'] }} pesanan masuk
            </div>
        </div>

        {{-- Card 3: Volume Produk Terjual --}}
        <div class="ss-stat-card" style="border-top:3px solid var(--color-ocean-navy);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <span class="ss-stat-card__label">Volume Terjual</span>
                <span style="font-size:18px;">⚖️</span>
            </div>
            <div class="ss-stat-card__value">{{ $metrics['total_weight_kg'] }} kg</div>
            <div class="ss-stat-card__sub">Total kuantitas olahan laut</div>
        </div>

        {{-- Card 4: Rata-rata Nilai Pesanan --}}
        <div class="ss-stat-card" style="border-top:3px solid var(--color-info);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <span class="ss-stat-card__label">Rata-rata Transaksi (AOV)</span>
                <span style="font-size:18px;">📈</span>
            </div>
            <div class="ss-stat-card__value" style="font-size:22px;">
                Rp {{ number_format($metrics['average_order_value'], 0, ',', '.') }}
            </div>
            <div class="ss-stat-card__sub">Per pesanan pembeli</div>
        </div>

    </div>
</section>

{{-- ── 2. BREAKDOWN PRODUK TERLARIS (Top Selling Products) ─────────── --}}
<section class="ss-surface-card" style="margin-bottom:var(--sp-6);">
    <div style="margin-bottom:var(--sp-4);">
        <h3 class="ss-h3" style="margin-bottom:2px;">Performa Penjualan per Produk</h3>
        <p class="ss-caption">Daftar produk olahan laut yang paling berkontribusi pada pendapatan toko.</p>
    </div>

    <div class="ss-table-wrapper">
        <table class="ss-table">
            <thead>
                <tr>
                    <th style="width:60px;">No</th>
                    <th>Nama Produk</th>
                    <th>Harga Satuan</th>
                    <th>Volume Terjual</th>
                    <th style="text-align:right;">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $index => $item)
                <tr>
                    <td>
                        <span style="width:24px;height:24px;border-radius:var(--radius-full);background:{{ $index === 0 ? 'var(--color-sand)' : 'var(--color-surface)' }};display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:var(--color-ink);">
                            {{ $index + 1 }}
                        </span>
                    </td>
                    <td>
                        <strong style="color:var(--color-ink);">{{ $item['name'] }}</strong>
                    </td>
                    <td>Rp {{ number_format($item['price'], 0, ',', '.') }} / {{ $item['unit'] }}</td>
                    <td>
                        <strong style="color:var(--color-sea-teal);">{{ $item['total_qty'] }} {{ $item['unit'] }}</strong>
                    </td>
                    <td style="text-align:right;">
                        <strong style="color:var(--color-ocean-navy);font-size:15px;">
                            Rp {{ number_format($item['total_revenue'], 0, ',', '.') }}
                        </strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:var(--sp-6);color:var(--color-muted);">
                        Belum ada penjualan tercatat pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- ── 3. CATATAN KEUANGAN & ARUS KAS USAHA ─────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-4);">
    <div class="ss-surface-card">
        <h3 class="ss-h3" style="font-size:15px;margin-bottom:var(--sp-2);">Informasi Arus Kas UMKM</h3>
        <p class="ss-caption" style="line-height:1.6;margin-bottom:var(--sp-3);">
            Semua transaksi pada SeaSupply menggunakan pencatatan kas bersih (tunai/transfer langsung saat pesanan diproses atau diselesaikan).
        </p>
        <div style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:var(--sp-3);">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span class="ss-caption">Status Pembukuan</span>
                <span class="ss-badge ss-badge--completed" style="font-size:10px;">Lancar</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span class="ss-caption">Rekening Pencairan Usaha</span>
                <span style="font-size:12px;font-weight:600;color:var(--color-ink);">BCA •••• 4892 (a.n Hasan)</span>
            </div>
        </div>
    </div>

    <div class="ss-surface-card">
        <h3 class="ss-h3" style="font-size:15px;margin-bottom:var(--sp-2);">Tindakan Cepat</h3>
        <p class="ss-caption" style="line-height:1.6;margin-bottom:var(--sp-3);">
            Ingin menambah varian produk baru atau melihat pesanan yang perlu disiapkan hari ini?
        </p>
        <div style="display:flex;gap:var(--sp-2);flex-wrap:wrap;">
            <a href="{{ route('owner.orders') }}" class="ss-btn ss-btn--primary ss-btn--sm">
                Lihat Pesanan Masuk
            </a>
            <a href="{{ route('owner.products') }}" class="ss-btn ss-btn--outline ss-btn--sm">
                Kelola Stok Produk
            </a>
        </div>
    </div>
</div>

@endsection
