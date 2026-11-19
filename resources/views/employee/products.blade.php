@extends('layouts.employee')

@section('title', 'Ketersediaan Produk')
@section('header-title', 'Pengecekan stok produk')

@section('content')
<section class="ss-surface-card" style="margin-bottom:var(--sp-6);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);">
        <div>
            <h2 class="ss-h3" style="margin-bottom:2px;">Stok Produk Toko</h2>
            <p class="ss-caption">Informasi ketersediaan produk olahan laut untuk persiapan operasional pesanan.</p>
        </div>
    </div>

    <div class="ss-table-wrapper">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga Jual</th>
                    <th>Stok Tersedia</th>
                    <th>Status Ketersediaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                <tr>
                    <td>
                        <strong style="color:var(--color-ink);">{{ $p['name'] }}</strong>
                    </td>
                    <td>{{ $p['category'] }}</td>
                    <td>Rp {{ number_format($p['price'], 0, ',', '.') }} / {{ $p['unit'] }}</td>
                    <td>
                        <strong style="{{ $p['stock'] === 0 ? 'color:var(--color-danger);' : ($p['stock'] <= 10 ? 'color:var(--color-warning);' : 'color:var(--color-ink);') }}">
                            {{ $p['stock'] }} {{ $p['unit'] }}
                        </strong>
                    </td>
                    <td>
                        @if($p['stock'] === 0)
                            <span class="ss-badge ss-badge--out-of-stock">Stok Habis</span>
                        @elseif($p['stock'] <= 10)
                            <span class="ss-badge ss-badge--pending">Menipis</span>
                        @else
                            <span class="ss-badge ss-badge--available">Tersedia</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
