@extends('layouts.employee')

@section('title', 'Daftar Pesanan Operasional')
@section('header-title', 'Tugas operasional')

@section('content')

{{-- ── 1. BANNER AKSES TERBATAS & TOMBOL KASIR CEPAT ────────────────── --}}
<div class="ss-surface-card" style="margin-bottom:var(--sp-5);background:var(--color-surface);border:1.5px solid var(--color-border);padding:var(--sp-4);">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:var(--sp-3);flex-wrap:wrap;">
        <div style="display:flex;align-items:flex-start;gap:var(--sp-3);flex:1;min-width:240px;">
            <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:var(--color-info-bg);color:var(--color-info);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                🛡️
            </div>
            <div>
                <h2 class="ss-h3" style="font-size:15px;margin-bottom:2px;color:var(--color-ink);">Akses terbatas</h2>
                <p class="ss-caption" style="color:var(--color-muted);line-height:1.5;">
                    Fitur mengikuti izin dari pemilik usaha. Anda dapat mengelola pesanan online maupun mencatat pembelian tunai langsung di toko.
                </p>
            </div>
        </div>

        <button type="button" class="ss-btn ss-btn--navy" id="btnQuickSale" style="flex-shrink:0;">
            🛒 Catat Penjualan Langsung
        </button>
    </div>
</div>

{{-- ── 2. DAFTAR PESANAN (Tabel Wireframe D) ────────────────────────── --}}
<section class="ss-surface-card" aria-label="Daftar Pesanan Operasional">
    
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-2);">
        <div>
            <h2 class="ss-h3" style="margin-bottom:2px;">Daftar pesanan</h2>
            <p class="ss-caption">Pesanan yang perlu ditangani akan tampil di sini.</p>
        </div>

        {{-- Filter Tab Status --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a href="{{ route('employee.orders', ['status' => 'all']) }}" 
               class="ss-chip {{ !request('status') || request('status') === 'all' ? 'active' : '' }}" style="padding:4px 12px;font-size:11px;">
                Semua
            </a>
            <a href="{{ route('employee.orders', ['status' => 'pending']) }}" 
               class="ss-chip {{ request('status') === 'pending' ? 'active' : '' }}" style="padding:4px 12px;font-size:11px;">
                Menunggu ({{ $counts['pending'] ?? 0 }})
            </a>
            <a href="{{ route('employee.orders', ['status' => 'processing']) }}" 
               class="ss-chip {{ request('status') === 'processing' ? 'active' : '' }}" style="padding:4px 12px;font-size:11px;">
                Diproses ({{ $counts['processing'] ?? 0 }})
            </a>
            <a href="{{ route('employee.orders', ['status' => 'shipped']) }}" 
               class="ss-chip {{ request('status') === 'shipped' ? 'active' : '' }}" style="padding:4px 12px;font-size:11px;">
                Dikirim ({{ $counts['shipped'] ?? 0 }})
            </a>
            <a href="{{ route('employee.orders', ['status' => 'cancelled']) }}" 
               class="ss-chip {{ request('status') === 'cancelled' ? 'active' : '' }}" style="padding:4px 12px;font-size:11px;">
                Dibatalkan ({{ $counts['cancelled'] ?? 0 }})
            </a>
        </div>
    </div>

    {{-- Tabel Pesanan Operasional Desktop --}}
    <div class="ss-table-wrapper ss-desktop-table">
        <table class="ss-table">
            <thead>
                <tr>
                    <th style="width:140px;">ID Pesanan</th>
                    <th>Ringkasan</th>
                    <th style="width:140px;">Status</th>
                    <th style="text-align:right;width:220px;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr id="orderRow-{{ $order['order_code'] }}">
                    {{-- ID Pesanan --}}
                    <td>
                        <strong style="color:var(--color-ocean-navy);font-family:monospace;font-size:14px;">
                            #{{ $order['order_code'] }}
                        </strong>
                        <div class="ss-caption">{{ $order['date'] }}</div>
                    </td>

                    {{-- Ringkasan --}}
                    <td>
                        <div style="font-weight:600;color:var(--color-ink);">
                            {{ $order['item_name'] }}
                        </div>
                        <div class="ss-caption" style="display:flex;gap:8px;align-items:center;margin-top:2px;">
                            <span>Kuantitas: <strong>{{ $order['quantity'] }} {{ $order['unit'] }}</strong></span>
                            <span>&bull;</span>
                            <span>Pembeli: <strong>{{ $order['buyer_name'] }}</strong></span>
                            <span>&bull;</span>
                            <span>Total: Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</span>
                        </div>
                    </td>

                    {{-- Status Badge --}}
                    <td id="badgeCell-{{ $order['order_code'] }}">
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

                    {{-- Tindakan Operasional --}}
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:6px;justify-content:flex-end;" id="actionsContainer-{{ $order['order_code'] }}">
                            @if($order['status'] === 'pending')
                                <button class="ss-btn ss-btn--primary ss-btn--sm btn-action-step" 
                                        data-code="{{ $order['order_code'] }}"
                                        data-next-status="processing"
                                        data-next-label="Diproses">
                                    Proses
                                </button>
                            @elseif($order['status'] === 'processing')
                                <button class="ss-btn ss-btn--primary ss-btn--sm btn-action-step" 
                                        style="background:var(--color-sea-teal);"
                                        data-code="{{ $order['order_code'] }}"
                                        data-next-status="shipped"
                                        data-next-label="Dikirim">
                                    Kirim
                                </button>
                            @elseif($order['status'] === 'shipped')
                                <button class="ss-btn ss-btn--primary ss-btn--sm btn-action-step" 
                                        style="background:var(--color-success);border-color:var(--color-success);"
                                        data-code="{{ $order['order_code'] }}"
                                        data-next-status="completed"
                                        data-next-label="Selesai">
                                    Selesai
                                </button>
                            @endif

                            <button class="ss-btn ss-btn--outline ss-btn--sm btn-view-detail" 
                                    data-order="{{ json_encode($order) }}"
                                    aria-label="Detail pesanan {{ $order['order_code'] }}">
                                Detail
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:var(--sp-7);color:var(--color-muted);">
                        <div style="font-size:32px;margin-bottom:8px;">📦</div>
                        <div class="ss-h3" style="margin-bottom:4px;font-size:14px;">Tidak ada pesanan aktif</div>
                        <p class="ss-caption">Pesanan yang perlu ditangani akan tampil di sini.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards (Layar <= 640px) --}}
    <div class="ss-mobile-cards">
        @forelse($orders as $order)
        <div class="ss-mobile-order-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <strong style="color:var(--color-ocean-navy);font-family:monospace;">#{{ $order['order_code'] }}</strong>
                <span class="ss-badge ss-badge--{{ $order['status'] }}">
                    {{ $order['status'] === 'pending' ? 'Menunggu' : ($order['status'] === 'processing' ? 'Diproses' : ($order['status'] === 'shipped' ? 'Dikirim' : ($order['status'] === 'completed' ? 'Selesai' : 'Dibatalkan'))) }}
                </span>
            </div>
            <div style="font-weight:600;font-size:13px;">{{ $order['item_name'] }} ({{ $order['quantity'] }} {{ $order['unit'] }})</div>
            <div class="ss-caption">{{ $order['buyer_name'] }} &bull; Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</div>
            <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
                @if($order['status'] === 'pending')
                    <button class="ss-btn ss-btn--primary ss-btn--sm btn-action-step" style="flex:1;"
                            data-code="{{ $order['order_code'] }}"
                            data-next-status="processing"
                            data-next-label="Diproses">
                        Proses
                    </button>
                @elseif($order['status'] === 'processing')
                    <button class="ss-btn ss-btn--primary ss-btn--sm btn-action-step" style="flex:1;background:var(--color-sea-teal);"
                            data-code="{{ $order['order_code'] }}"
                            data-next-status="shipped"
                            data-next-label="Dikirim">
                        Kirim
                    </button>
                @elseif($order['status'] === 'shipped')
                    <button class="ss-btn ss-btn--primary ss-btn--sm btn-action-step" style="flex:1;background:var(--color-success);border-color:var(--color-success);"
                            data-code="{{ $order['order_code'] }}"
                            data-next-status="completed"
                            data-next-label="Selesai">
                        Selesai
                    </button>
                @endif
                <button type="button" class="ss-btn ss-btn--outline ss-btn--sm btn-view-detail" style="flex:1;" data-order="{{ json_encode($order) }}">
                    Detail
                </button>
            </div>
        </div>
        @empty
        <div class="ss-caption" style="text-align:center;padding:var(--sp-4);">Tidak ada pesanan aktif.</div>
        @endforelse
    </div>

</section>

{{-- ── 3. MODAL KASIR CEPAT / PENJUALAN OFFLINE LANGSUNG ──────────── --}}
<div class="ss-modal-overlay" id="modalQuickSale" role="dialog" aria-modal="true" aria-labelledby="quickSaleTitle">
    <div class="ss-modal" style="max-width:490px;max-height:92vh;overflow-y:auto;">
        <h2 class="ss-modal__title" id="quickSaleTitle">🛒 Kasir Cepat — Catat Penjualan Langsung</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">
            Catat transaksi pembelian tunai di tempat secara instan. Stok produk otomatis terpotong.
        </p>

        <div id="quickSaleError" class="ss-alert ss-alert--danger" style="display:none;padding:8px 12px;font-size:12px;margin-bottom:var(--sp-3);" role="alert">
            <span id="quickSaleErrorMsg"></span>
        </div>

        <form id="formQuickSale" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            <div class="ss-input-group">
                <label class="ss-label" for="qsProduct">Pilih Produk *</label>
                <select id="qsProduct" class="ss-select" required>
                    @foreach($products as $prod)
                        <option value="{{ $prod['id'] }}"
                                data-price="{{ $prod['price'] }}"
                                data-stock="{{ $prod['stock'] }}"
                                data-unit="{{ $prod['unit'] }}"
                                {{ $prod['stock'] <= 0 ? 'disabled' : '' }}>
                            {{ $prod['name'] }} — Rp {{ number_format($prod['price'], 0, ',', '.') }}/{{ $prod['unit'] }} (Stok: {{ $prod['stock'] }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ss-two-col-grid">
                <div class="ss-input-group">
                    <label class="ss-label">Jumlah Beli *</label>
                    <div class="ss-qty">
                        <button type="button" class="ss-qty__btn" id="qsQtyDec">−</button>
                        <div class="ss-qty__value" id="qsQtyVal">1</div>
                        <button type="button" class="ss-qty__btn" id="qsQtyInc">+</button>
                    </div>
                    <span class="ss-caption" id="qsStockInfo">Tersedia: -</span>
                </div>

                <div class="ss-input-group">
                    <label class="ss-label" for="qsMethod">Metode Bayar</label>
                    <select id="qsMethod" class="ss-select">
                        <option value="Tunai di Tempat">Tunai di Tempat</option>
                        <option value="QRIS Kasir">QRIS Kasir</option>
                    </select>
                </div>
            </div>

            <div class="ss-two-col-grid">
                <div class="ss-input-group">
                    <label class="ss-label" for="qsBuyerName">Nama Pembeli</label>
                    <input type="text" id="qsBuyerName" class="ss-input" value="Pembeli Langsung (Walk-in)" placeholder="Nama pembeli">
                </div>
                <div class="ss-input-group">
                    <label class="ss-label" for="qsBuyerPhone">No. HP (Opsional)</label>
                    <input type="tel" id="qsBuyerPhone" class="ss-input" placeholder="0812-xxxx-xxxx">
                </div>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="qsCashReceived">Uang Diterima (Rp)</label>
                <input type="number" id="qsCashReceived" class="ss-input" min="0" step="1000" placeholder="Masukkan nominal uang tunai (opsional)">
            </div>

            {{-- Ringkasan Total & Kembalian --}}
            <div style="background:var(--color-seafoam-bg);border:1px solid var(--color-seafoam);border-radius:var(--radius-sm);padding:var(--sp-3);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span style="font-size:13px;font-weight:600;color:var(--color-ocean-navy);">Total Tagihan</span>
                    <strong id="qsTotalDisplay" style="font-size:20px;color:var(--color-ocean-navy);">Rp 0</strong>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;">
                    <span style="color:var(--color-muted);">Kembalian</span>
                    <strong id="qsChangeDisplay" style="color:var(--color-sea-teal);">Rp 0</strong>
                </div>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-2);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnCancelQuickSale">Batal</button>
                <button type="submit" class="ss-btn ss-btn--primary" id="btnSubmitQuickSale">Simpan Penjualan Tunai</button>
            </div>
        </form>
    </div>
</div>

{{-- ── 3. MODAL DETAIL OPERASIONAL & PEMBATALAN ───────────────────── --}}
<div class="ss-modal-overlay" id="modalOperasional" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:460px;">
        <h2 class="ss-modal__title" id="opModalTitle">Rincian Operasional Pesanan</h2>
        
        <div id="opModalBody" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:var(--sp-4);margin:var(--sp-3) 0;">
            {{-- Dynamic via JS --}}
        </div>

        <div class="ss-modal__actions" style="display:flex;justify-content:space-between;align-items:center;">
            <div id="cancelOrderActionArea">
                {{-- Tombol Batalkan Pesanan (jika status belum selesai atau cancelled) --}}
            </div>
            <button type="button" class="ss-btn ss-btn--outline" id="btnCloseOpModal">Tutup</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. Tombol Aksi Cepat Operasional (Proses -> Kirim -> Selesai)
    document.querySelectorAll('.btn-action-step').forEach(btn => {
        btn.addEventListener('click', function () {
            const code       = this.dataset.code;
            const nextStatus = this.dataset.nextStatus;
            const nextLabel  = this.dataset.nextLabel;
            const currentBtn = this;

            currentBtn.disabled = true;

            updateOrderStatusAjax(code, nextStatus, function (data) {
                currentBtn.disabled = false;

                // Update badge tampilan
                const badgeCell = document.getElementById('badgeCell-' + code);
                if (badgeCell) {
                    let badgeClass = 'ss-badge--' + nextStatus;
                    badgeCell.innerHTML = `<span class="ss-badge ${badgeClass}">${nextLabel}</span>`;
                }

                // Update atau hilangkan tombol tindakan
                if (nextStatus === 'processing') {
                    currentBtn.dataset.nextStatus = 'shipped';
                    currentBtn.dataset.nextLabel  = 'Dikirim';
                    currentBtn.textContent        = 'Kirim';
                    currentBtn.style.background   = 'var(--color-sea-teal)';
                } else if (nextStatus === 'shipped') {
                    currentBtn.dataset.nextStatus = 'completed';
                    currentBtn.dataset.nextLabel  = 'Selesai';
                    currentBtn.textContent        = 'Selesai';
                    currentBtn.style.background   = 'var(--color-success)';
                    currentBtn.style.borderColor  = 'var(--color-success)';
                } else {
                    currentBtn.remove();
                }

                showToast(`✓ Pesanan #${code} berhasil diperbarui: ${nextLabel}`, 'success');
            }, function (errMsg) {
                currentBtn.disabled = false;
                alert('Gagal memperbarui status: ' + errMsg);
            });
        });
    });

    // 2. Modal Detail & Pembatalan Pesanan
    const modal                = document.getElementById('modalOperasional');
    const closeBtn             = document.getElementById('btnCloseOpModal');
    const modalBody            = document.getElementById('opModalBody');
    const cancelActionArea     = document.getElementById('cancelOrderActionArea');
    let currentSelectedOrder   = null;

    document.querySelectorAll('.btn-view-detail').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.order);
            currentSelectedOrder = data;

            let statusBadge = `<span class="ss-badge ss-badge--${data.status}">${data.status}</span>`;

            modalBody.innerHTML = `
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Kode Pesanan</span>
                    <strong style="color:var(--color-ocean-navy);font-family:monospace;">#${data.order_code}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Pembeli</span>
                    <span style="font-weight:500;">${data.buyer_name} (${data.buyer_phone})</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Produk Pesanan</span>
                    <span style="font-weight:600;">${data.item_name}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Jumlah yang Disiapkan</span>
                    <strong style="color:var(--color-ocean-navy);">${data.quantity} ${data.unit}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding-top:8px;border-top:1px solid var(--color-border);">
                    <span class="ss-caption">Status Pesanan</span>
                    ${statusBadge}
                </div>
            `;

            // Tampilkan tombol Batalkan jika status belum completed atau cancelled
            cancelActionArea.innerHTML = '';
            if (data.status !== 'completed' && data.status !== 'cancelled') {
                const btnCancel = document.createElement('button');
                btnCancel.type = 'button';
                btnCancel.className = 'ss-btn ss-btn--danger-soft ss-btn--sm';
                btnCancel.textContent = 'Batalkan Pesanan (Kembalikan Stok)';
                btnCancel.addEventListener('click', function () {
                    if (!confirm(`Konfirmasi pembatalan pesanan #${data.order_code}? Kuantitas stok produk akan otomatis dikembalikan ke katalog.`)) {
                        return;
                    }

                    btnCancel.disabled = true;
                    updateOrderStatusAjax(data.order_code, 'cancelled', function (resp) {
                        modal.classList.remove('open');
                        showToast(`✓ Pesanan #${data.order_code} berhasil dibatalkan. Kuantitas stok otomatis dikembalikan.`, 'success');
                        
                        // Update UI row
                        const badgeCell = document.getElementById('badgeCell-' + data.order_code);
                        if (badgeCell) {
                            badgeCell.innerHTML = `<span class="ss-badge ss-badge--cancelled">Dibatalkan</span>`;
                        }
                        const actionContainer = document.getElementById('actionsContainer-' + data.order_code);
                        if (actionContainer) {
                            const stepBtn = actionContainer.querySelector('.btn-action-step');
                            if (stepBtn) stepBtn.remove();
                        }
                        setTimeout(() => window.location.reload(), 1200);
                    }, function (errMsg) {
                        btnCancel.disabled = false;
                        alert('Gagal membatalkan pesanan: ' + errMsg);
                    });
                });
                cancelActionArea.appendChild(btnCancel);
            }

            modal.classList.add('open');
        });
    });

    closeBtn.addEventListener('click', () => modal.classList.remove('open'));
    if (modal) {
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
    }

    function updateOrderStatusAjax(orderCode, status, onSuccess, onError) {
        fetch('/employee/orders/' + orderCode + '/status', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify({ status: status })
        })
        .then(res => {
            if (!res.ok) return res.json().then(err => { throw err; });
            return res.json();
        })
        .then(data => onSuccess(data))
        .catch(err => {
            const msg = err.error?.message || err.message || 'Terjadi kesalahan sistem.';
            if (onError) onError(msg);
        });
    }

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'ss-toast ss-toast--' + (type || 'success');
        toast.textContent = message;
        document.getElementById('toastContainer').appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }

    // ── 3. KASIR CEPAT / CATAT PENJUALAN LANGSUNG OFFLINE ────────────
    const modalQS = document.getElementById('modalQuickSale');
    const btnOpenQS = document.getElementById('btnQuickSale');
    const btnCancelQS = document.getElementById('btnCancelQuickSale');
    const formQS = document.getElementById('formQuickSale');
    const qsProduct = document.getElementById('qsProduct');
    const qsQtyVal = document.getElementById('qsQtyVal');
    const qsQtyDec = document.getElementById('qsQtyDec');
    const qsQtyInc = document.getElementById('qsQtyInc');
    const qsStockInfo = document.getElementById('qsStockInfo');
    const qsTotalDisplay = document.getElementById('qsTotalDisplay');
    const qsCashInput = document.getElementById('qsCashReceived');
    const qsChangeDisplay = document.getElementById('qsChangeDisplay');
    const qsErrorBox = document.getElementById('quickSaleError');
    const qsErrorMsg = document.getElementById('quickSaleErrorMsg');
    const btnSubmitQS = document.getElementById('btnSubmitQuickSale');

    let qsQty = 1;

    function getSelectedProdMeta() {
        const opt = qsProduct.options[qsProduct.selectedIndex];
        if (!opt) return { price: 0, stock: 0, unit: 'kg' };
        return {
            price: parseInt(opt.dataset.price || 0, 10),
            stock: parseInt(opt.dataset.stock || 0, 10),
            unit: opt.dataset.unit || 'kg'
        };
    }

    function refreshQuickSaleCalc() {
        const meta = getSelectedProdMeta();
        if (meta.stock > 0 && qsQty > meta.stock) qsQty = meta.stock;
        if (qsQty < 1) qsQty = 1;

        qsQtyVal.textContent = qsQty;
        qsQtyDec.disabled = qsQty <= 1;
        qsQtyInc.disabled = qsQty >= meta.stock;
        qsStockInfo.textContent = `Tersedia: ${meta.stock} ${meta.unit}`;

        const total = meta.price * qsQty;
        qsTotalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');

        const cashVal = parseInt(qsCashInput.value || 0, 10);
        if (qsCashInput.value === '') {
            qsChangeDisplay.textContent = 'Rp 0';
            qsChangeDisplay.style.color = 'var(--color-sea-teal)';
        } else {
            const diff = cashVal - total;
            if (diff < 0) {
                qsChangeDisplay.textContent = 'Kurang Rp ' + Math.abs(diff).toLocaleString('id-ID');
                qsChangeDisplay.style.color = 'var(--color-danger)';
            } else {
                qsChangeDisplay.textContent = 'Rp ' + diff.toLocaleString('id-ID');
                qsChangeDisplay.style.color = 'var(--color-success)';
            }
        }
    }

    if (btnOpenQS) {
        btnOpenQS.addEventListener('click', () => {
            qsQty = 1;
            qsErrorBox.style.display = 'none';
            refreshQuickSaleCalc();
            modalQS.classList.add('open');
        });
    }

    if (btnCancelQS) btnCancelQS.addEventListener('click', () => modalQS.classList.remove('open'));
    if (modalQS) modalQS.addEventListener('click', (e) => { if (e.target === modalQS) modalQS.classList.remove('open'); });
    if (qsProduct) qsProduct.addEventListener('change', () => { qsQty = 1; refreshQuickSaleCalc(); });
    if (qsQtyDec) qsQtyDec.addEventListener('click', () => { if (qsQty > 1) { qsQty--; refreshQuickSaleCalc(); } });
    if (qsQtyInc) qsQtyInc.addEventListener('click', () => { const m = getSelectedProdMeta(); if (qsQty < m.stock) { qsQty++; refreshQuickSaleCalc(); } });
    if (qsCashInput) qsCashInput.addEventListener('input', refreshQuickSaleCalc);

    if (formQS) {
        formQS.addEventListener('submit', function (e) {
            e.preventDefault();
            const meta = getSelectedProdMeta();
            if (meta.stock <= 0) {
                qsErrorMsg.textContent = 'Produk yang dipilih sedang kehabisan stok.';
                qsErrorBox.style.display = 'flex';
                return;
            }

            btnSubmitQS.disabled = true;
            btnSubmitQS.textContent = 'Menyimpan...';
            qsErrorBox.style.display = 'none';

            fetch('{{ route('employee.orders.quick_sale') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    product_id: parseInt(qsProduct.value, 10),
                    quantity: qsQty,
                    buyer_name: document.getElementById('qsBuyerName').value.trim(),
                    buyer_phone: document.getElementById('qsBuyerPhone').value.trim(),
                    payment_method: document.getElementById('qsMethod').value,
                    cash_received: qsCashInput.value ? parseInt(qsCashInput.value, 10) : null
                })
            })
            .then(res => res.json().then(body => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                btnSubmitQS.disabled = false;
                btnSubmitQS.textContent = 'Simpan Penjualan Tunai';

                if (!ok) {
                    qsErrorMsg.textContent = body.error?.message || body.message || 'Gagal mencatat penjualan.';
                    qsErrorBox.style.display = 'flex';
                    return;
                }

                modalQS.classList.remove('open');
                showToast('✓ ' + body.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(() => {
                btnSubmitQS.disabled = false;
                btnSubmitQS.textContent = 'Simpan Penjualan Tunai';
                qsErrorMsg.textContent = 'Terjadi kesalahan jaringan saat menyimpan transaksi.';
                qsErrorBox.style.display = 'flex';
            });
        });
    }
});
</script>
@endpush
