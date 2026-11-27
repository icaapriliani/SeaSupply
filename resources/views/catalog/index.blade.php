@extends('layouts.buyer')

@section('title', 'Katalog Produk')

@section('content')
<div class="ss-page">

    {{-- ── HERO SECTION ─────────────────────────────────── --}}
    <section class="ss-hero" aria-label="Banner utama SeaSupply">
        <div class="ss-hero__content">
            <div class="ss-hero__eyebrow">🌊 Marketplace UMKM Olahan Laut</div>
            <h1 class="ss-hero__title">Temukan produk<br>olahan laut terbaik</h1>
            <p class="ss-hero__subtitle">
                Belanja langsung dari UMKM lokal — segar, berkualitas,<br>dan mendukung nelayan Indonesia.
            </p>
            <a href="#produk-pilihan" class="ss-hero__cta">
                <span>Jelajahi Produk</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>
        <div class="ss-hero__visual" aria-hidden="true">
            <div class="ss-hero__icon-card">🦀<span>Rajungan</span></div>
            <div class="ss-hero__icon-card">🐟<span>Ikan Kering</span></div>
            <div class="ss-hero__icon-card">🌾<span>Kerupuk</span></div>
        </div>
    </section>

    {{-- ── KATEGORI PRODUK ──────────────────────────────── --}}
    <section class="ss-categories" aria-label="Filter kategori produk">
        <div class="ss-section-header">
            <h2 class="ss-section-title">Kategori produk</h2>
            <span class="ss-caption">{{ count($products) }} produk ditemukan</span>
        </div>
        <div class="ss-category-chips" role="list">
            @foreach($categories as $cat)
            <a href="{{ route('catalog.index', array_merge(
                    request()->only('search'),
                    $cat['id'] === 'all' ? [] : ['category_id' => $cat['id']]
               )) }}"
               class="ss-chip {{ $activeCategory === $cat['id'] ? 'active' : '' }}"
               role="listitem"
               aria-pressed="{{ $activeCategory === $cat['id'] ? 'true' : 'false' }}">
                <span class="ss-chip__icon">{{ $cat['icon'] }}</span>
                {{ $cat['name'] }}
            </a>
            @endforeach
        </div>
    </section>

    {{-- ── SEARCH RESULT ALERT ──────────────────────────── --}}
    @if($search)
    <div class="ss-alert ss-alert--info" role="status">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        Hasil pencarian untuk <strong>"{{ $search }}"</strong> — {{ count($products) }} produk ditemukan.
        <a href="{{ route('catalog.index') }}" style="margin-left:8px;font-weight:600;text-decoration:underline;color:inherit;">
            Reset pencarian
        </a>
    </div>
    @endif

    {{-- ── PRODUCT SECTION ──────────────────────────────── --}}
    <section class="ss-products" id="produk-pilihan" aria-label="Daftar produk">
        <div class="ss-section-header">
            <h2 class="ss-section-title" id="sectionProductTitle">
                @if($search)
                    Hasil Pencarian
                @elseif($activeCategory !== 'all')
                    @php $catName = collect($categories)->firstWhere('id', $activeCategory)['name'] ?? 'Produk'; @endphp
                    {{ $catName }}
                @else
                    Produk pilihan
                @endif
            </h2>
            <a href="{{ route('catalog.index') }}" class="ss-section-link" id="linkLihatSemua" style="{{ $activeCategory !== 'all' || $search ? '' : 'display:none;' }}">
                Lihat semua
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>

        {{-- SKELETON LOADER GRID (MICRO-INTERACTION SAAT MEMUAT / FILTER) --}}
        <div class="ss-product-grid" id="skeletonGrid" style="display:none;" aria-hidden="true">
            @for($i = 0; $i < 4; $i++)
            <div class="ss-card ss-card--skeleton" style="pointer-events:none;">
                <div class="ss-card__image ss-skeleton" style="aspect-ratio:4/3;background-size:600px 100%;"></div>
                <div class="ss-card__body">
                    <div class="ss-skeleton" style="height:12px;width:60px;margin-bottom:6px;"></div>
                    <div class="ss-skeleton" style="height:18px;width:85%;margin-bottom:8px;"></div>
                    <div class="ss-skeleton" style="height:12px;width:70%;margin-bottom:12px;"></div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;padding-top:8px;">
                        <div class="ss-skeleton" style="height:20px;width:80px;"></div>
                        <div class="ss-skeleton" style="height:14px;width:50px;"></div>
                    </div>
                </div>
                <div class="ss-card__footer">
                    <div class="ss-skeleton" style="height:36px;width:100%;border-radius:var(--radius-sm);"></div>
                </div>
            </div>
            @endfor
        </div>

        @if(count($products) === 0)
        {{-- EMPTY STATE --}}
        <div class="ss-empty" id="staticEmptyState" role="status">
            <div class="ss-empty__icon">🌊</div>
            <div class="ss-empty__title">Produk tidak ditemukan</div>
            <p class="ss-empty__text">
                @if($search)
                    Tidak ada produk yang cocok dengan kata kunci <em>"{{ $search }}"</em>.
                @else
                    Belum ada produk pada kategori ini.
                @endif
            </p>
            <a href="{{ route('catalog.index') }}" class="ss-btn ss-btn--outline" style="margin-top:8px;">
                Lihat semua produk
            </a>
        </div>

        @else
        {{-- DYNAMIC EMPTY STATE (UNTUK FILTER JS) --}}
        <div class="ss-empty" id="dynamicEmptyState" style="display:none;" role="status">
            <div class="ss-empty__icon">🌊</div>
            <div class="ss-empty__title">Produk tidak ditemukan</div>
            <p class="ss-empty__text">
                Belum ada produk pada kategori ini.
            </p>
            <a href="{{ route('catalog.index') }}" class="ss-btn ss-btn--outline btn-reset-category" style="margin-top:8px;">
                Lihat semua produk
            </a>
        </div>

        {{-- PRODUCT GRID --}}
        <div class="ss-product-grid" id="productGrid" role="list">
            @foreach($products as $product)
            <article class="ss-card ss-card--animated" data-category="{{ $product['category_id'] }}" role="listitem">

                {{-- Image / Emoji Placeholder --}}
                <div class="ss-card__image" style="position:relative;">
                    <div class="ss-card__image-placeholder"
                         style="background:{{ $product['gradient'] }};height:100%;position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:52px;filter:drop-shadow(0 2px 6px rgba(0,0,0,.12));">{{ $product['emoji'] }}</span>
                    </div>
                    @if(! $product['is_available'])
                    <div style="position:absolute;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:1;">
                        <span class="ss-badge ss-badge--out-of-stock">Stok Habis</span>
                    </div>
                    @endif
                </div>

                {{-- Body --}}
                <div class="ss-card__body">
                    <div class="ss-card__category">{{ $product['category'] }}</div>
                    <h3 class="ss-card__name">{{ $product['name'] }}</h3>
                    <div class="ss-card__store">
                        <svg class="ss-card__store-icon" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        </svg>
                        {{ $product['store_name'] }} &bull; {{ $product['store_location'] }}
                    </div>
                    <div class="ss-card__meta">
                        <div class="ss-card__price">
                            Rp {{ number_format($product['price'], 0, ',', '.') }}
                            <span class="ss-card__price-unit">/ {{ $product['unit'] }}</span>
                        </div>
                        <div class="ss-card__stock {{ $product['stock'] === 0 ? 'out' : ($product['stock'] <= 10 ? 'low' : '') }}"
                             aria-label="Stok: {{ $product['stock'] }}">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                            </svg>
                            @if($product['stock'] === 0)
                                Habis
                            @elseif($product['stock'] <= 10)
                                Sisa {{ $product['stock'] }}
                            @else
                                Stok {{ $product['stock'] }}
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer CTA --}}
                <div class="ss-card__footer">
                    @if($product['is_available'])
                    <a href="{{ route('catalog.show', $product['id']) }}"
                       class="ss-btn ss-btn--primary ss-btn--full">
                        Lihat detail
                    </a>
                    @else
                    <button class="ss-btn ss-btn--disabled ss-btn--full" disabled aria-disabled="true">
                        Stok Habis
                    </button>
                    @endif
                </div>

            </article>
            @endforeach
        </div>
        @endif
    </section>

    {{-- ── WHY SEASUPPLY ────────────────────────────────── --}}
    @if(! $search && $activeCategory === 'all')
    <section style="margin-bottom:var(--sp-6);" aria-label="Keunggulan SeaSupply">
        <div class="ss-section-header">
            <h2 class="ss-section-title">Mengapa SeaSupply?</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--sp-4);margin-top:var(--sp-3);">
            @foreach([
                ['🏪','Langsung dari UMKM','Belanja langsung dari produsen lokal, tanpa perantara.'],
                ['🐟','Produk Segar & Berkualitas','Semua produk dipilih dari tangkapan dan budidaya terpercaya.'],
                ['🤝','Dukung Nelayan Lokal','Setiap pembelian Anda mendukung perekonomian nelayan Indonesia.'],
                ['📦','Pemesanan Mudah','Proses pemesanan sederhana dan transparan dari awal hingga selesai.'],
            ] as [$icon, $judul, $desc])
            <div class="ss-surface-card" style="text-align:center;padding:var(--sp-5);">
                <div style="font-size:36px;margin-bottom:var(--sp-3);" aria-hidden="true">{{ $icon }}</div>
                <div class="ss-h3" style="margin-bottom:var(--sp-1);">{{ $judul }}</div>
                <p class="ss-caption">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Smooth scroll CTA hero
    const cta = document.querySelector('.ss-hero__cta[href="#produk-pilihan"]');
    if (cta) {
        cta.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('produk-pilihan')?.scrollIntoView({ behavior: 'smooth' });
        });
    }

    // Highlight kata pencarian di nama produk
    const q = @json($search);
    if (q) {
        const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
        document.querySelectorAll('.ss-card__name').forEach(el => {
            el.innerHTML = el.innerHTML.replace(re,
                '<mark style="background:rgba(22,140,131,.18);border-radius:2px;padding:0 2px;">$1</mark>');
        });
    }

    // ── INTERAKTIVITAS: ANIMASI TRANSISI & SKELETON LOADER SAAT FILTER KATEGORI ──
    const chips            = document.querySelectorAll('.ss-category-chips .ss-chip');
    const productGrid      = document.getElementById('productGrid');
    const skeletonGrid     = document.getElementById('skeletonGrid');
    const dynamicEmpty     = document.getElementById('dynamicEmptyState');
    const sectionTitle     = document.getElementById('sectionProductTitle');
    const linkLihatSemua   = document.getElementById('linkLihatSemua');
    const cards            = document.querySelectorAll('#productGrid .ss-card');

    if (chips.length > 0 && productGrid && skeletonGrid) {
        chips.forEach(chip => {
            chip.addEventListener('click', function (e) {
                // Jangan cegah default jika sedang dalam mode search backend
                if (q) return;

                e.preventDefault();
                const targetUrl = new URL(this.href, window.location.origin);
                const categorySlug = targetUrl.searchParams.get('category_id') || 'all';
                const catName = this.textContent.trim();

                // 1. Update status active pada chips
                chips.forEach(c => {
                    c.classList.remove('active');
                    c.setAttribute('aria-pressed', 'false');
                });
                this.classList.add('active');
                this.setAttribute('aria-pressed', 'true');

                // 2. Micro-interaction: Tampilkan skeleton loader & sembunyikan grid sementara
                productGrid.style.display = 'none';
                if (dynamicEmpty) dynamicEmpty.style.display = 'none';
                skeletonGrid.style.display = 'grid';

                // 3. Update judul section & tombol lihat semua
                if (sectionTitle) {
                    sectionTitle.textContent = categorySlug === 'all' ? 'Produk pilihan' : catName;
                }
                if (linkLihatSemua) {
                    linkLihatSemua.style.display = categorySlug === 'all' ? 'none' : 'inline-flex';
                }

                // 4. Simulasi transisi pemuatan data yang halus (~260ms)
                setTimeout(() => {
                    skeletonGrid.style.display = 'none';

                    let visibleCount = 0;
                    cards.forEach(card => {
                        const cardCat = card.getAttribute('data-category');
                        if (categorySlug === 'all' || cardCat === categorySlug) {
                            card.style.display = '';
                            card.classList.remove('ss-card--animated');
                            void card.offsetWidth; // Force reflow for restart animation
                            card.classList.add('ss-card--animated');
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0) {
                        if (dynamicEmpty) dynamicEmpty.style.display = 'flex';
                    } else {
                        productGrid.style.display = 'grid';
                    }

                    // 5. Update browser URL history tanpa reload
                    history.pushState({ category: categorySlug }, '', targetUrl.pathname + targetUrl.search);
                }, 260);
            });
        });

        // Tangani reset filter pada empty state
        document.querySelectorAll('.btn-reset-category').forEach(btn => {
            btn.addEventListener('click', function (e) {
                const allChip = document.querySelector('.ss-category-chips .ss-chip');
                if (allChip) {
                    allChip.click();
                    e.preventDefault();
                }
            });
        });
    }
});
</script>
@endpush
