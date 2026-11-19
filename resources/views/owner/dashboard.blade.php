@extends('layouts.owner')

@section('title', 'Dashboard Pemilik Usaha')
@section('header-title', 'Dashboard pemilik usaha')

@section('content')

{{-- ── 1. RINGKASAN KARTU METRIK (Wireframe C & Design System) ─────── --}}
<section aria-label="Ringkasan statistik toko" style="margin-bottom: var(--sp-6);">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:var(--sp-4);">
        
        {{-- Card 1: Produk Aktif --}}
        <div class="ss-stat-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <span class="ss-stat-card__label">Produk aktif</span>
                <span style="width:32px;height:32px;border-radius:var(--radius-sm);background:var(--color-seafoam-bg);color:var(--color-sea-teal);display:flex;align-items:center;justify-content:center;font-size:16px;">
                    📦
                </span>
            </div>
            <div class="ss-stat-card__value">{{ $stats['active_products_count'] }}</div>
            <div class="ss-stat-card__sub" style="color:var(--color-success);display:flex;align-items:center;gap:4px;">
                <span>●</span> {{ $stats['active_products_count'] }} produk siap dipesan
            </div>
        </div>

        {{-- Card 2: Pesanan Masuk --}}
        <div class="ss-stat-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <span class="ss-stat-card__label">Pesanan masuk</span>
                <span style="width:32px;height:32px;border-radius:var(--radius-sm);background:var(--color-warning-bg);color:var(--color-warning);display:flex;align-items:center;justify-content:center;font-size:16px;">
                    🛒
                </span>
            </div>
            <div class="ss-stat-card__value">{{ $stats['incoming_orders_count'] }}</div>
            <div class="ss-stat-card__sub" style="color:var(--color-warning);display:flex;align-items:center;gap:4px;">
                <span>●</span> Memerlukan tindakan segera
            </div>
        </div>

        {{-- Card 3: Penjualan --}}
        <div class="ss-stat-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <span class="ss-stat-card__label">Penjualan</span>
                <span style="width:32px;height:32px;border-radius:var(--radius-sm);background:var(--color-info-bg);color:var(--color-info);display:flex;align-items:center;justify-content:center;font-size:16px;">
                    💰
                </span>
            </div>
            <div class="ss-stat-card__value">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</div>
            <div class="ss-stat-card__sub">Periode bulan ini</div>
        </div>

    </div>
</section>

{{-- ── 2. PESANAN TERBARU (Wireframe C Table) ─────────────────────── --}}
<section class="ss-surface-card" style="margin-bottom:var(--sp-6);" aria-label="Tabel Pesanan Terbaru">
    
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);">
        <div>
            <h2 class="ss-h3" style="margin-bottom:2px;">Pesanan terbaru</h2>
            <p class="ss-caption">Daftar transaksi pesanan masuk dari pembeli.</p>
        </div>
        <a href="{{ route('owner.orders') }}" class="ss-section-link">
            Lihat semua pesanan
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </a>
    </div>

    {{-- Tabel Pesanan --}}
    <div class="ss-table-wrapper">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Pembeli</th>
                    <th>Produk & Jumlah</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align:right;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                <tr>
                    {{-- ID Pesanan --}}
                    <td>
                        <strong style="color:var(--color-ocean-navy);font-family:monospace;font-size:13px;">
                            #{{ $order['order_code'] }}
                        </strong>
                        <div class="ss-caption">{{ $order['date'] }}</div>
                    </td>

                    {{-- Pembeli --}}
                    <td>
                        <div style="font-weight:500;">{{ $order['buyer_name'] }}</div>
                        <div class="ss-caption">{{ $order['buyer_phone'] }}</div>
                    </td>

                    {{-- Produk & Kuantitas --}}
                    <td>
                        <div style="font-size:var(--fs-body);">{{ $order['item_name'] }}</div>
                        <div class="ss-caption">{{ $order['quantity'] }} {{ $order['unit'] }}</div>
                    </td>

                    {{-- Total --}}
                    <td>
                        <span style="font-weight:600;color:var(--color-ink);">
                            Rp {{ number_format($order['total_amount'], 0, ',', '.') }}
                        </span>
                    </td>

                    {{-- Status Badge (Design System Tokens) --}}
                    <td>
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
                    </td>

                    {{-- Tindakan --}}
                    <td style="text-align:right;">
                        <button class="ss-btn ss-btn--outline ss-btn--sm btn-detail-order" 
                                data-order="{{ json_encode($order) }}"
                                aria-label="Lihat detail pesanan {{ $order['order_code'] }}">
                            Lihat detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:var(--sp-6);color:var(--color-muted);">
                        Belum ada pesanan terbaru.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tombol Aksi Bawah (Sesuai Wireframe C) --}}
    <div style="display:flex;gap:var(--sp-3);margin-top:var(--sp-5);padding-top:var(--sp-4);border-top:1px solid var(--color-border);flex-wrap:wrap;">
        <button class="ss-btn ss-btn--navy" id="btnTambahProduk">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah produk
        </button>

        <a href="{{ route('owner.employees') }}" class="ss-btn ss-btn--outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            </svg>
            Atur pegawai
        </a>
    </div>

</section>

{{-- ── 3. MODAL TAMBAH PRODUK (Wireframe C Action) ────────────────── --}}
<div class="ss-modal-overlay" id="modalTambahProduk" role="dialog" aria-modal="true" aria-labelledby="modalTambahTitle">
    <div class="ss-modal" style="max-width:480px;">
        <h2 class="ss-modal__title" id="modalTambahTitle">
            Tambah Produk Olahan Laut
        </h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">Masukkan informasi produk baru untuk ditampilkan di katalog pembeli.</p>

        <form id="formTambahProduk" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            <div class="ss-input-group">
                <label class="ss-label" for="prodName">Nama Produk *</label>
                <input type="text" class="ss-input" id="prodName" placeholder="Contoh: Olahan Rajungan Pedas" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3);">
                <div class="ss-input-group">
                    <label class="ss-label" for="prodCategory">Kategori *</label>
                    <select class="ss-select" id="prodCategory" required>
                        <option value="Rajungan">Rajungan</option>
                        <option value="Ikan Kering">Ikan Kering</option>
                        <option value="Kerupuk Ikan">Kerupuk Ikan</option>
                        <option value="Udang Olahan">Udang Olahan</option>
                        <option value="Cumi-cumi">Cumi-cumi</option>
                        <option value="Kepiting">Kepiting</option>
                    </select>
                </div>
                <div class="ss-input-group">
                    <label class="ss-label" for="prodUnit">Satuan</label>
                    <input type="text" class="ss-input" id="prodUnit" value="kg" placeholder="kg, bungkus, pak">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3);">
                <div class="ss-input-group">
                    <label class="ss-label" for="prodPrice">Harga (Rp) *</label>
                    <input type="number" class="ss-input" id="prodPrice" placeholder="50000" min="0" required>
                </div>
                <div class="ss-input-group">
                    <label class="ss-label" for="prodStock">Stok Awal *</label>
                    <input type="number" class="ss-input" id="prodStock" placeholder="25" min="0" required>
                </div>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="prodDesc">Deskripsi Produk</label>
                <textarea class="ss-input" id="prodDesc" rows="3" style="height:auto;padding:8px var(--sp-3);resize:vertical;" placeholder="Keterangan rasa, ketahanan simpan, atau cara saji..."></textarea>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnBatalTambah">Batal</button>
                <button type="submit" class="ss-btn ss-btn--navy">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

{{-- ── 4. MODAL DETAIL & UBAH STATUS PESANAN ──────────────────────── --}}
<div class="ss-modal-overlay" id="modalDetailOrder" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:440px;">
        <h2 class="ss-modal__title" id="orderModalTitle">Detail Pesanan</h2>
        
        <div id="orderModalContent" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:var(--sp-3);margin:var(--sp-3) 0;">
            {{-- Dynamic via JS --}}
        </div>

        <div class="ss-input-group" style="margin-bottom:var(--sp-4);">
            <label class="ss-label" for="selectOrderStatus">Ubah Status Pesanan</label>
            <select class="ss-select" id="selectOrderStatus">
                <option value="pending">Menunggu</option>
                <option value="processing">Diproses</option>
                <option value="shipped">Dikirim</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>

        <div class="ss-modal__actions">
            <button type="button" class="ss-btn ss-btn--outline" id="btnCloseOrderModal">Tutup</button>
            <button type="button" class="ss-btn ss-btn--primary" id="btnUpdateStatus">Simpan Status</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal Tambah Produk
    const modalTambah   = document.getElementById('modalTambahProduk');
    const btnTambah     = document.getElementById('btnTambahProduk');
    const btnBatalTambah= document.getElementById('btnBatalTambah');
    const formTambah    = document.getElementById('formTambahProduk');

    btnTambah.addEventListener('click', () => modalTambah.classList.add('open'));
    btnBatalTambah.addEventListener('click', () => modalTambah.classList.remove('open'));

    formTambah.addEventListener('submit', function (e) {
        e.preventDefault();
        const nama = document.getElementById('prodName').value;
        modalTambah.classList.remove('open');
        formTambah.reset();

        showToast('✓ Produk "' + nama + '" berhasil ditambahkan!', 'success');
    });

    // Modal Detail Order & Update Status
    const modalOrder      = document.getElementById('modalDetailOrder');
    const btnCloseOrder   = document.getElementById('btnCloseOrderModal');
    const btnUpdateStatus = document.getElementById('btnUpdateStatus');
    const orderContent    = document.getElementById('orderModalContent');
    const selectStatus    = document.getElementById('selectOrderStatus');
    let currentOrderCode  = '';

    document.querySelectorAll('.btn-detail-order').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.order);
            currentOrderCode = data.order_code;

            orderContent.innerHTML = `
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span class="ss-caption">Kode Pesanan</span>
                    <strong style="color:var(--color-ocean-navy);">#${data.order_code}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span class="ss-caption">Pembeli</span>
                    <span style="font-weight:500;">${data.buyer_name} (${data.buyer_phone})</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span class="ss-caption">Item</span>
                    <span>${data.item_name} &bull; ${data.quantity} ${data.unit}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding-top:6px;border-top:1px solid var(--color-border);">
                    <span style="font-weight:600;">Total</span>
                    <strong style="color:var(--color-ocean-navy);">Rp ${parseInt(data.total_amount).toLocaleString('id-ID')}</strong>
                </div>
            `;

            selectStatus.value = data.status;
            modalOrder.classList.add('open');
        });
    });

    btnCloseOrder.addEventListener('click', () => modalOrder.classList.remove('open'));

    btnUpdateStatus.addEventListener('click', function () {
        const newStatus = selectStatus.value;
        modalOrder.classList.remove('open');
        showToast('✓ Status pesanan #' + currentOrderCode + ' diperbarui menjadi: ' + newStatus, 'success');
    });

    // Helper Toast
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'ss-toast ss-toast--' + (type || 'success');
        toast.textContent = message;
        document.getElementById('toastContainer').appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }
});
</script>
@endpush
