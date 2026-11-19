@extends('layouts.employee')

@section('title', 'Daftar Pesanan Operasional')
@section('header-title', 'Tugas operasional')

@section('content')

{{-- ── 1. BANNER AKSES TERBATAS (Sesuai Wireframe D) ────────────────── --}}
<div class="ss-surface-card" style="margin-bottom:var(--sp-5);background:var(--color-surface);border:1.5px solid var(--color-border);padding:var(--sp-4);">
    <div style="display:flex;align-items:flex-start;gap:var(--sp-3);">
        <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:var(--color-info-bg);color:var(--color-info);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
            🛡️
        </div>
        <div>
            <h2 class="ss-h3" style="font-size:15px;margin-bottom:2px;color:var(--color-ink);">Akses terbatas</h2>
            <p class="ss-caption" style="color:var(--color-muted);line-height:1.5;">
                Fitur mengikuti izin dari pemilik usaha. Anda memiliki hak akses untuk melihat pesanan dan memperbarui status operasional harian.
            </p>
        </div>
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
        <div style="display:flex;gap:6px;">
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
        </div>
    </div>

    {{-- Tabel Pesanan Operasional --}}
    <div class="ss-table-wrapper">
        <table class="ss-table">
            <thead>
                <tr>
                    <th style="width:140px;">ID Pesanan</th>
                    <th>Ringkasan</th>
                    <th style="width:140px;">Status</th>
                    <th style="text-align:right;width:200px;">Tindakan</th>
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
                        <div style="display:inline-flex;gap:6px;justify-content:flex-end;">
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

</section>

{{-- ── 3. MODAL DETAIL OPERASIONAL ─────────────────────────────────── --}}
<div class="ss-modal-overlay" id="modalOperasional" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:440px;">
        <h2 class="ss-modal__title" id="opModalTitle">Rincian Operasional Pesanan</h2>
        
        <div id="opModalBody" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:var(--sp-4);margin:var(--sp-3) 0;">
            {{-- Dynamic via JS --}}
        </div>

        <div class="ss-modal__actions">
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

            fetch('/employee/orders/' + code + '/status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ status: nextStatus })
            })
            .then(res => res.json())
            .then(data => {
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

                showToast(`✓ Pesanan #${code} berhasil diperbarui di database: ${nextLabel}`, 'success');
            })
            .catch(err => {
                currentBtn.disabled = false;
                alert('Gagal memperbarui status di database.');
            });
        });
    });

    // 2. Modal Detail
    const modal     = document.getElementById('modalOperasional');
    const closeBtn  = document.getElementById('btnCloseOpModal');
    const modalBody = document.getElementById('opModalBody');

    document.querySelectorAll('.btn-view-detail').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.order);

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
                    <span class="ss-caption">Status</span>
                    <span class="ss-badge ss-badge--${data.status}">${data.status}</span>
                </div>
            `;

            modal.classList.add('open');
        });
    });

    closeBtn.addEventListener('click', () => modal.classList.remove('open'));
    if (modal) {
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
    }

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
