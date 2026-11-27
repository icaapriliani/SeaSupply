@extends('layouts.buyer')

@section('title', 'Pesanan Saya')

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
            <li aria-current="page" style="color:var(--color-ink);font-weight:600;">Pesanan Saya</li>
        </ol>
    </nav>

    {{-- ── SECTION HEADER & FILTER ──────────────────────── --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-3);">
        <div>
            <h1 class="ss-h2" style="margin-bottom:4px;">Riwayat Pesanan Anda</h1>
            <p class="ss-caption">Pantau status pemrosesan dan pengiriman produk olahan laut yang Anda pesan.</p>
        </div>

        {{-- Filter Tab Status --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a href="{{ route('catalog.orders') }}" 
               class="ss-chip {{ !request('status') || request('status') === 'all' ? 'active' : '' }}" style="padding:5px 14px;">
                Semua ({{ count($allOrders) }})
            </a>
            <a href="{{ route('catalog.orders', ['status' => 'pending']) }}" 
               class="ss-chip {{ request('status') === 'pending' ? 'active' : '' }}" style="padding:5px 14px;">
                Menunggu
            </a>
            <a href="{{ route('catalog.orders', ['status' => 'processing']) }}" 
               class="ss-chip {{ request('status') === 'processing' ? 'active' : '' }}" style="padding:5px 14px;">
                Diproses
            </a>
            <a href="{{ route('catalog.orders', ['status' => 'shipped']) }}" 
               class="ss-chip {{ request('status') === 'shipped' ? 'active' : '' }}" style="padding:5px 14px;">
                Dikirim
            </a>
            <a href="{{ route('catalog.orders', ['status' => 'completed']) }}" 
               class="ss-chip {{ request('status') === 'completed' ? 'active' : '' }}" style="padding:5px 14px;">
                Selesai
            </a>
        </div>
    </div>

    @if(count($orders) === 0)
    {{-- ── EMPTY STATE ──────────────────────────────────── --}}
    <div class="ss-surface-card" style="text-align:center;padding:var(--sp-8) var(--sp-4);">
        <div style="font-size:48px;margin-bottom:var(--sp-3);" aria-hidden="true">🛒</div>
        <h2 class="ss-h3" style="margin-bottom:var(--sp-1);">Belum ada pesanan</h2>
        <p class="ss-caption" style="max-width:320px;margin:0 auto var(--sp-4);">
            Anda belum memiliki pesanan dengan filter status ini. Silakan jelajahi katalog untuk menemukan produk olahan laut segar.
        </p>
        <a href="{{ route('catalog.index') }}" class="ss-btn ss-btn--primary">
            Jelajahi Katalog Sekarang
        </a>
    </div>

    @else
    {{-- ── LIST PESANAN (Kombinasi Cards & Table sesuai Design System) ── --}}
    <div style="display:flex;flex-direction:column;gap:var(--sp-4);">
        @foreach($orders as $order)
        <article class="ss-surface-card" style="padding:var(--sp-4);transition:box-shadow .15s;" 
                 onmouseover="this.style.boxShadow='var(--shadow-medium)'" 
                 onmouseout="this.style.boxShadow='var(--shadow-low)'">
            
            {{-- Header Kartu Pesanan --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:var(--sp-3);border-bottom:1px solid var(--color-border);flex-wrap:wrap;gap:var(--sp-2);">
                <div style="display:flex;align-items:center;gap:var(--sp-3);">
                    <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:var(--color-seafoam-bg);color:var(--color-sea-teal);display:flex;align-items:center;justify-content:center;font-size:18px;">
                        📦
                    </div>
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <strong style="color:var(--color-ocean-navy);font-family:monospace;font-size:15px;">
                                #{{ $order['order_code'] }}
                            </strong>
                            <span class="ss-caption">&bull; {{ $order['date'] }}</span>
                        </div>
                        <div class="ss-caption" style="display:flex;align-items:center;gap:4px;margin-top:2px;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            </svg>
                            Penjual: <strong>{{ $order['store_name'] }}</strong> ({{ $order['store_location'] }})
                        </div>
                    </div>
                </div>

                {{-- Status Badge --}}
                <div>
                    @if($order['status'] === 'pending')
                        <span class="ss-badge ss-badge--pending">Menunggu</span>
                    @elseif($order['status'] === 'processing')
                        <span class="ss-badge ss-badge--processing">Diproses</span>
                    @elseif($order['status'] === 'shipped')
                        <span class="ss-badge ss-badge--shipped">Dikirim</span>
                    @elseif($order['status'] === 'completed')
                        <span class="ss-badge ss-badge--completed">Selesai</span>
                    @elseif($order['status'] === 'cancelled')
                        <span class="ss-badge ss-badge--cancelled">Dibatalkan</span>
                    @endif
                </div>
            </div>

            {{-- Body: Item Pesanan Snapshot --}}
            <div style="padding:var(--sp-3) 0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:var(--sp-3);">
                <div>
                    <div style="font-weight:600;font-size:15px;color:var(--color-ink);">
                        {{ $order['item_name'] }}
                    </div>
                    <div class="ss-caption" style="margin-top:2px;">
                        {{ $order['quantity'] }} {{ $order['unit'] }} &times; Rp {{ number_format($order['unit_price'], 0, ',', '.') }}
                    </div>
                    @if($order['recipient_name'])
                    <div class="ss-caption" style="margin-top:4px;color:var(--color-muted);">
                        Penerima: {{ $order['recipient_name'] }} ({{ $order['recipient_phone'] }})
                    </div>
                    @endif
                </div>

                <div style="text-align:right;">
                    <div class="ss-caption">Total Pembayaran</div>
                    <div style="font-size:18px;font-weight:700;color:var(--color-ocean-navy);">
                        Rp {{ number_format($order['subtotal'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Footer Kartu: Bantuan & Detail --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:var(--sp-3);border-top:1px dashed var(--color-border);flex-wrap:wrap;gap:var(--sp-2);">
                <span class="ss-caption" style="color:var(--color-muted);">
                    @if($order['status'] === 'pending')
                        ⏳ Pesanan Anda sedang menunggu verifikasi oleh toko.
                    @elseif($order['status'] === 'processing')
                        📦 Toko sedang menyiapkan dan mengemas produk olahan laut Anda.
                    @elseif($order['status'] === 'shipped')
                        🚚 Produk dalam perjalanan menuju alamat Anda.
                    @elseif($order['status'] === 'completed')
                        ✓ Pesanan telah selesai diterima. Terima kasih!
                    @endif
                </span>

                <button class="ss-btn ss-btn--outline ss-btn--sm btn-show-order-detail" 
                        data-order="{{ json_encode($order) }}">
                    Rincian Pesanan
                </button>
            </div>

        </article>
        @endforeach
    </div>
    @endif

</div>

{{-- ── MODAL RINCIAN PESANAN ───────────────────────────── --}}
<div class="ss-modal-overlay" id="modalBuyerOrder" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:460px;">
        <h2 class="ss-modal__title" id="buyerModalTitle">Rincian Transaksi</h2>
        
        <div id="buyerModalContent" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:var(--sp-4);margin:var(--sp-3) 0;">
            {{-- Dynamic via JS --}}
        </div>

        <div class="ss-modal__actions">
            <button type="button" class="ss-btn ss-btn--primary ss-btn--full" id="btnCloseBuyerModal">
                Tutup
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal     = document.getElementById('modalBuyerOrder');
    const closeBtn  = document.getElementById('btnCloseBuyerModal');
    const modalBody = document.getElementById('buyerModalContent');

    document.querySelectorAll('.btn-show-order-detail').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.order);

            modalBody.innerHTML = `
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Kode Transaksi</span>
                    <strong style="color:var(--color-ocean-navy);font-family:monospace;font-size:15px;">#${data.order_code}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Tanggal Pesan</span>
                    <span style="font-weight:500;">${data.date}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Toko Penjual</span>
                    <span style="font-weight:600;color:var(--color-sea-teal);">${data.store_name}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Status Saat Ini</span>
                    <span class="ss-badge ss-badge--${data.status}">${data.status}</span>
                </div>
                <div style="margin:10px 0;padding:10px;background:white;border-radius:var(--radius-xs);border:1px solid var(--color-border);">
                    <div style="font-weight:600;margin-bottom:4px;">${data.item_name}</div>
                    <div class="ss-caption">${data.quantity} ${data.unit} &times; Rp ${parseInt(data.unit_price).toLocaleString('id-ID')}</div>
                </div>
                ${data.recipient_name || data.delivery_address ? `
                <div style="margin:10px 0;padding:10px;background:white;border-radius:var(--radius-xs);border:1px solid var(--color-border);font-size:12px;">
                    <div style="font-weight:600;color:var(--color-ocean-navy);margin-bottom:4px;">📍 Alamat & Penerima</div>
                    <div><strong>${data.recipient_name || '-'}</strong> (${data.recipient_phone || '-'})</div>
                    <div style="color:var(--color-muted);margin-top:2px;">${data.delivery_address || 'Alamat tidak dicantumkan'}</div>
                    ${data.buyer_note ? `<div style="margin-top:6px;padding-top:4px;border-top:1px dashed var(--color-border);color:var(--color-sea-teal);"><strong>Catatan:</strong> ${data.buyer_note}</div>` : ''}
                </div>
                ` : ''}
                <div style="display:flex;justify-content:space-between;padding-top:8px;border-top:1px solid var(--color-border);">
                    <span style="font-weight:700;">Subtotal Akhir</span>
                    <strong style="color:var(--color-ocean-navy);font-size:16px;">Rp ${parseInt(data.subtotal).toLocaleString('id-ID')}</strong>
                </div>
            `;

            modal.classList.add('open');
        });
    });

    closeBtn.addEventListener('click', () => modal.classList.remove('open'));
    if (modal) {
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
    }
});
</script>
@endpush
