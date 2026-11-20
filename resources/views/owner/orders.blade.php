@extends('layouts.owner')

@section('title', 'Manajemen Pesanan')
@section('header-title', 'Kelola Pesanan Masuk')

@section('content')

{{-- ── FILTER TABS & INFO ──────────────────────────────────────────── --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-3);">
    <div>
        <h2 class="ss-h3" style="margin-bottom:2px;">Daftar Transaksi Pesanan Toko</h2>
        <p class="ss-caption">Pantau dan ubah status pesanan masuk dari pembeli secara langsung.</p>
    </div>

    {{-- Filter Tab Status --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <a href="{{ route('owner.orders') }}" 
           class="ss-chip {{ !request('status') || request('status') === 'all' ? 'active' : '' }}" style="padding:5px 12px;font-size:11px;">
            Semua ({{ count($allOrders) }})
        </a>
        <a href="{{ route('owner.orders', ['status' => 'pending']) }}" 
           class="ss-chip {{ request('status') === 'pending' ? 'active' : '' }}" style="padding:5px 12px;font-size:11px;">
            Menunggu ({{ $counts['pending'] ?? 0 }})
        </a>
        <a href="{{ route('owner.orders', ['status' => 'processing']) }}" 
           class="ss-chip {{ request('status') === 'processing' ? 'active' : '' }}" style="padding:5px 12px;font-size:11px;">
            Diproses ({{ $counts['processing'] ?? 0 }})
        </a>
        <a href="{{ route('owner.orders', ['status' => 'shipped']) }}" 
           class="ss-chip {{ request('status') === 'shipped' ? 'active' : '' }}" style="padding:5px 12px;font-size:11px;">
            Dikirim ({{ $counts['shipped'] ?? 0 }})
        </a>
        <a href="{{ route('owner.orders', ['status' => 'completed']) }}" 
           class="ss-chip {{ request('status') === 'completed' ? 'active' : '' }}" style="padding:5px 12px;font-size:11px;">
            Selesai ({{ $counts['completed'] ?? 0 }})
        </a>
    </div>
</div>

{{-- ── TABEL PESANAN ───────────────────────────────────────────────── --}}
<section class="ss-surface-card" style="margin-bottom:var(--sp-6);">
    <div class="ss-table-wrapper">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Pembeli & Kontak</th>
                    <th>Produk Pesanan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align:right;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                <tr id="rowOrder-{{ $o['order_code'] }}">
                    {{-- ID Pesanan --}}
                    <td>
                        <strong style="color:var(--color-ocean-navy);font-family:monospace;font-size:14px;">
                            #{{ $o['order_code'] }}
                        </strong>
                        <div class="ss-caption">{{ $o['date'] }}</div>
                    </td>

                    {{-- Pembeli --}}
                    <td>
                        <div style="font-weight:600;">{{ $o['buyer_name'] }}</div>
                        <div class="ss-caption">{{ $o['buyer_phone'] }}</div>
                    </td>

                    {{-- Produk & Kuantitas --}}
                    <td>
                        <div style="font-size:var(--fs-body);font-weight:500;">{{ $o['item_name'] }}</div>
                        <div class="ss-caption">{{ $o['quantity'] }} {{ $o['unit'] }}</div>
                    </td>

                    {{-- Total --}}
                    <td>
                        <strong style="color:var(--color-ocean-navy);">
                            Rp {{ number_format($o['total_amount'], 0, ',', '.') }}
                        </strong>
                    </td>

                    {{-- Status Badge --}}
                    <td id="statusCell-{{ $o['order_code'] }}">
                        @if($o['status'] === 'pending')
                            <span class="ss-badge ss-badge--pending">Menunggu</span>
                        @elseif($o['status'] === 'processing')
                            <span class="ss-badge ss-badge--processing">Diproses</span>
                        @elseif($o['status'] === 'shipped')
                            <span class="ss-badge ss-badge--shipped">Dikirim</span>
                        @elseif($o['status'] === 'completed')
                            <span class="ss-badge ss-badge--completed">Selesai</span>
                        @elseif($o['status'] === 'cancelled')
                            <span class="ss-badge ss-badge--cancelled">Dibatalkan</span>
                        @endif
                    </td>

                    {{-- Tindakan --}}
                    <td style="text-align:right;">
                        <button class="ss-btn ss-btn--outline ss-btn--sm btn-manage-order"
                                data-order="{{ json_encode($o) }}">
                            Ubah Status
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:var(--sp-7);color:var(--color-muted);">
                        Tidak ada pesanan pada filter ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- ── MODAL UBAH STATUS PESANAN ───────────────────────────────────── --}}
<div class="ss-modal-overlay" id="modalManageOrder" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:440px;">
        <h2 class="ss-modal__title">Kelola Status Pesanan</h2>
        
        <div id="manageOrderContent" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:var(--sp-3);margin:var(--sp-3) 0;">
            {{-- Dynamic via JS --}}
        </div>

        <div class="ss-input-group" style="margin-bottom:var(--sp-4);">
            <label class="ss-label" for="selectManageStatus">Pilih Status Baru</label>
            <select class="ss-select" id="selectManageStatus">
                <option value="pending">Menunggu</option>
                <option value="processing">Diproses</option>
                <option value="shipped">Dikirim</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>

        <div class="ss-modal__actions">
            <button type="button" class="ss-btn ss-btn--outline" id="btnCancelManage">Tutup</button>
            <button type="button" class="ss-btn ss-btn--primary" id="btnSaveManageStatus">Simpan Status</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const modal     = document.getElementById('modalManageOrder');
    const btnCancel = document.getElementById('btnCancelManage');
    const btnSave   = document.getElementById('btnSaveManageStatus');
    const content   = document.getElementById('manageOrderContent');
    const select    = document.getElementById('selectManageStatus');
    let activeCode  = '';

    document.querySelectorAll('.btn-manage-order').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.order);
            activeCode = data.order_code;

            content.innerHTML = `
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span class="ss-caption">Kode Pesanan</span>
                    <strong style="color:var(--color-ocean-navy);">#${data.order_code}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span class="ss-caption">Pembeli</span>
                    <span style="font-weight:500;">${data.buyer_name} (${data.buyer_phone})</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span class="ss-caption">Produk</span>
                    <span>${data.item_name} &bull; ${data.quantity} ${data.unit}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding-top:6px;border-top:1px solid var(--color-border);">
                    <span style="font-weight:600;">Total</span>
                    <strong style="color:var(--color-ocean-navy);">Rp ${parseInt(data.total_amount).toLocaleString('id-ID')}</strong>
                </div>
            `;

            select.value = data.status;
            modal.classList.add('open');
        });
    });

    btnCancel.addEventListener('click', () => modal.classList.remove('open'));

    btnSave.addEventListener('click', function () {
        const newStatus = select.value;
        btnSave.disabled = true;

        fetch('/owner/orders/' + activeCode + '/status', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            btnSave.disabled = false;
            modal.classList.remove('open');
            showToast('✓ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        });
    });

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
