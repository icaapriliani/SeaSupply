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
                        <div style="display:flex;align-items:center;gap:var(--sp-3);">
                            @if(!empty($p['image_url']))
                                <img src="{{ asset('storage/' . $p['image_url']) }}" alt="{{ $p['name'] }}"
                                     style="width:36px;height:36px;border-radius:var(--radius-sm);object-fit:cover;flex-shrink:0;border:1px solid var(--color-border);"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display:none;width:36px;height:36px;border-radius:var(--radius-sm);background:{{ $p['gradient'] }};align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                                    {{ $p['emoji'] }}
                                </div>
                            @else
                                <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:{{ $p['gradient'] }};display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                                    {{ $p['emoji'] }}
                                </div>
                            @endif
                            <strong style="color:var(--color-ink);">{{ $p['name'] }}</strong>
                        </div>
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
