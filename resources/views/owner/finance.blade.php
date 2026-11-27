@extends('layouts.owner')

@section('title', 'Laporan Keuangan & Penjualan')
@section('header-title', 'Laporan Keuangan & Penjualan Toko')

@section('content')

{{-- ── FILTER PERIODE PENJUALAN & AKSI PENGELUARAN (FR-10) ─────────── --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-3);">
    <div>
        <h2 class="ss-h3" style="margin-bottom:2px;">Analisis Performa Penjualan & Kas Toko</h2>
        <p class="ss-caption">Pantau pendapatan, catat pengeluaran operasional (FR-10), dan riwayat transaksi usaha Anda.</p>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
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

        <button type="button" class="ss-btn ss-btn--navy ss-btn--sm" id="btnTambahPengeluaran">
            + Tambah Pengeluaran Operasional
        </button>
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

{{-- ── 3. PENCATATAN KAS & PENGELUARAN OPERASIONAL (FR-10) ─────────── --}}
<section class="ss-surface-card" style="margin-bottom:var(--sp-6);border-top:3px solid var(--color-ocean-navy);" aria-label="Pencatatan Kas dan Pengeluaran Operasional">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap;">
        <div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:2px;">
                <span class="ss-badge" style="background:var(--color-seafoam-bg);color:var(--color-ocean-navy);">FR-10</span>
                <h3 class="ss-h3" style="margin:0;">Pencatatan Kas & Pengeluaran Operasional Toko</h3>
            </div>
            <p class="ss-caption">Catat biaya operasional harian untuk memantau kas bersih UMKM olahan laut.</p>
        </div>

        <button type="button" class="ss-btn ss-btn--primary ss-btn--sm" id="btnTambahPengeluaranSection">
            + Tambah Pengeluaran Operasional
        </button>
    </div>

    {{-- Ringkasan Kas Bersih --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:var(--sp-3);margin-bottom:var(--sp-4);">
        <div style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:var(--sp-3);">
            <div class="ss-caption">Pemasukan Penjualan</div>
            <strong style="font-size:17px;color:var(--color-sea-teal);">Rp {{ number_format($metrics['total_sales'], 0, ',', '.') }}</strong>
        </div>
        <div style="background:var(--color-danger-bg);border:1px solid #f0cbcb;border-radius:var(--radius-sm);padding:var(--sp-3);">
            <div class="ss-caption" style="color:var(--color-danger);">Total Pengeluaran Operasional</div>
            <strong id="totalExpenseDisplay" style="font-size:17px;color:var(--color-danger);">Rp 0</strong>
        </div>
        <div style="background:var(--color-seafoam-bg);border:1px solid var(--color-seafoam);border-radius:var(--radius-sm);padding:var(--sp-3);">
            <div class="ss-caption" style="color:var(--color-ocean-navy);">Estimasi Kas Bersih</div>
            <strong id="netCashDisplay" style="font-size:17px;color:var(--color-ocean-navy);">Rp {{ number_format($metrics['total_sales'], 0, ',', '.') }}</strong>
        </div>
    </div>

    {{-- Tabel Desktop Pengeluaran --}}
    <div class="ss-table-wrapper ss-desktop-table">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori Pengeluaran</th>
                    <th>Keterangan</th>
                    <th>Sumber Kas</th>
                    <th style="text-align:right;">Nominal</th>
                    <th style="text-align:right;width:90px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="expenseTableBody">
                {{-- Rendered via JS --}}
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards Pengeluaran --}}
    <div class="ss-mobile-cards" id="expenseMobileCards">
        {{-- Rendered via JS --}}
    </div>
</section>

{{-- ── 4. CATATAN KEUANGAN & ARUS KAS USAHA ─────────────────────────── --}}
<div class="ss-two-col-grid">
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

{{-- ── MODAL TAMBAH PENGELUARAN OPERASIONAL (FR-10) ─────────────────── --}}
<div class="ss-modal-overlay" id="modalTambahPengeluaran" role="dialog" aria-modal="true" aria-labelledby="modalExpenseTitle">
    <div class="ss-modal" style="max-width:480px;max-height:92vh;overflow-y:auto;">
        <h2 class="ss-modal__title" id="modalExpenseTitle">💸 Tambah Pengeluaran Operasional</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">
            Catat biaya operasional toko agar arus kas dan laba bersih tercatat akurat.
        </p>

        <form id="formTambahPengeluaran" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            <div class="ss-two-col-grid">
                <div class="ss-input-group">
                    <label class="ss-label" for="expDate">Tanggal Pengeluaran *</label>
                    <input type="date" id="expDate" class="ss-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="ss-input-group">
                    <label class="ss-label" for="expMethod">Sumber Kas *</label>
                    <select id="expMethod" class="ss-select" required>
                        <option value="Kas Tunai Toko">Kas Tunai Toko</option>
                        <option value="Transfer Rekening Usaha">Transfer Rekening Usaha</option>
                    </select>
                </div>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="expCategory">Kategori Pengeluaran *</label>
                <select id="expCategory" class="ss-select" required>
                    <option value="Es Batu & Pengawet">Es Batu &amp; Pengawet</option>
                    <option value="Garam Pengering">Garam Pengering</option>
                    <option value="Bahan Kemasan">Bahan Kemasan</option>
                    <option value="Solar/Transportasi">Solar/Transportasi</option>
                    <option value="Operasional Lainnya">Operasional Lainnya</option>
                </select>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="expAmount">Nominal Pengeluaran (Rp) *</label>
                <input type="number" id="expAmount" class="ss-input" min="1000" step="500" placeholder="Contoh: 75000" required>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="expNote">Keterangan / Rincian *</label>
                <textarea id="expNote" class="ss-input" rows="2" style="height:auto;padding:8px var(--sp-3);resize:vertical;" placeholder="Contoh: Pembelian 10 balok es batu & plastik vakum 2 pak" required></textarea>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnBatalPengeluaran">Batal</button>
                <button type="submit" class="ss-btn ss-btn--navy">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const EXPENSE_KEY = 'seasupply_owner_expenses_v1';
    const totalSales = {{ (int) $metrics['total_sales'] }};

    const defaultExpenses = [
        {
            id: 1,
            date: '{{ date('Y-m-d') }}',
            category: 'Es Batu & Pengawet',
            note: 'Es batu balok 8 buah untuk menjaga kesegaran rajungan & cumi',
            method: 'Kas Tunai Toko',
            amount: 80000
        },
        {
            id: 2,
            date: '{{ date('Y-m-d', strtotime('-1 day')) }}',
            category: 'Bahan Kemasan',
            note: 'Plastik vacuum food-grade & kotak styrofoam pengiriman',
            method: 'Transfer Rekening Usaha',
            amount: 145000
        },
        {
            id: 3,
            date: '{{ date('Y-m-d', strtotime('-2 days')) }}',
            category: 'Solar/Transportasi',
            note: 'BBM mobil pick-up pengantaran ke pelabuhan & ekspedisi',
            method: 'Kas Tunai Toko',
            amount: 120000
        }
    ];

    function readExpenses() {
        try {
            const raw = localStorage.getItem(EXPENSE_KEY);
            if (!raw) {
                localStorage.setItem(EXPENSE_KEY, JSON.stringify(defaultExpenses));
                return defaultExpenses;
            }
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : defaultExpenses;
        } catch (e) {
            return defaultExpenses;
        }
    }

    function saveExpenses(list) {
        localStorage.setItem(EXPENSE_KEY, JSON.stringify(list));
    }

    function formatRp(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    function renderExpenses() {
        const list = readExpenses();
        const tbody = document.getElementById('expenseTableBody');
        const mobileWrap = document.getElementById('expenseMobileCards');
        const totalEl = document.getElementById('totalExpenseDisplay');
        const netEl = document.getElementById('netCashDisplay');

        const totalExpense = list.reduce((sum, item) => sum + (parseInt(item.amount, 10) || 0), 0);
        const netCash = totalSales - totalExpense;

        totalEl.textContent = formatRp(totalExpense);
        netEl.textContent = (netCash < 0 ? '-' : '') + formatRp(Math.abs(netCash));
        netEl.style.color = netCash < 0 ? 'var(--color-danger)' : 'var(--color-ocean-navy)';

        if (list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:var(--sp-5);color:var(--color-muted);">Belum ada pengeluaran operasional tercatat.</td></tr>';
            mobileWrap.innerHTML = '<div class="ss-caption" style="text-align:center;padding:var(--sp-4);">Belum ada pengeluaran operasional tercatat.</div>';
            return;
        }

        tbody.innerHTML = list.map(item => `
            <tr>
                <td style="white-space:nowrap;">${item.date}</td>
                <td><span class="ss-chip" style="padding:2px 8px;font-size:11px;pointer-events:none;">${item.category}</span></td>
                <td>${item.note}</td>
                <td><span class="ss-caption" style="font-weight:600;color:var(--color-ink);">${item.method}</span></td>
                <td style="text-align:right;"><strong style="color:var(--color-danger);">- ${formatRp(item.amount)}</strong></td>
                <td style="text-align:right;">
                    <button type="button" class="ss-btn ss-btn--danger-soft ss-btn--sm btn-del-expense" data-id="${item.id}">Hapus</button>
                </td>
            </tr>
        `).join('');

        mobileWrap.innerHTML = list.map(item => `
            <div class="ss-mobile-order-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span class="ss-chip" style="padding:2px 8px;font-size:10px;pointer-events:none;">${item.category}</span>
                    <span class="ss-caption">${item.date}</span>
                </div>
                <div style="font-size:13px;color:var(--color-ink);margin:4px 0;">${item.note}</div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding-top:6px;border-top:1px dashed var(--color-border);">
                    <div>
                        <div class="ss-caption">${item.method}</div>
                        <strong style="color:var(--color-danger);">- ${formatRp(item.amount)}</strong>
                    </div>
                    <button type="button" class="ss-btn ss-btn--danger-soft ss-btn--sm btn-del-expense" data-id="${item.id}">Hapus</button>
                </div>
            </div>
        `).join('');
    }

    const modalExp = document.getElementById('modalTambahPengeluaran');
    const btnOpen1 = document.getElementById('btnTambahPengeluaran');
    const btnOpen2 = document.getElementById('btnTambahPengeluaranSection');
    const btnBatal = document.getElementById('btnBatalPengeluaran');
    const formExp = document.getElementById('formTambahPengeluaran');

    const openExpenseModal = () => modalExp.classList.add('open');
    if (btnOpen1) btnOpen1.addEventListener('click', openExpenseModal);
    if (btnOpen2) btnOpen2.addEventListener('click', openExpenseModal);
    btnBatal.addEventListener('click', () => modalExp.classList.remove('open'));
    modalExp.addEventListener('click', (e) => { if (e.target === modalExp) modalExp.classList.remove('open'); });

    formExp.addEventListener('submit', function (e) {
        e.preventDefault();
        const list = readExpenses();
        list.unshift({
            id: Date.now(),
            date: document.getElementById('expDate').value,
            category: document.getElementById('expCategory').value,
            method: document.getElementById('expMethod').value,
            amount: parseInt(document.getElementById('expAmount').value, 10),
            note: document.getElementById('expNote').value.trim()
        });
        saveExpenses(list);
        renderExpenses();
        modalExp.classList.remove('open');
        formExp.reset();
        document.getElementById('expDate').value = '{{ date('Y-m-d') }}';

        const toast = document.createElement('div');
        toast.className = 'ss-toast ss-toast--success';
        toast.textContent = '✓ Pengeluaran operasional berhasil dicatat ke kas.';
        document.getElementById('toastContainer').appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    });

    document.addEventListener('click', function (e) {
        const delBtn = e.target.closest('.btn-del-expense');
        if (!delBtn) return;
        const id = Number(delBtn.dataset.id);
        const list = readExpenses().filter(i => Number(i.id) !== id);
        saveExpenses(list);
        renderExpenses();
    });

    renderExpenses();
});
</script>
@endpush
