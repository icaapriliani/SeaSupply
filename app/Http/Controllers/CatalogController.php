<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CatalogController extends Controller
{
    // ── Mock Data Produk (akan diganti dengan Eloquent Model nanti) ──
    private function getProducts(): array
    {
        return [
            [
                'id'             => 1,
                'name'           => 'Olahan Rajungan Premium',
                'category'       => 'Rajungan',
                'category_id'    => 'rajungan',
                'store_name'     => 'Usaha Pak Hasan',
                'store_location' => 'Cirebon, Jabar',
                'price'          => 85000,
                'unit'           => 'kg',
                'stock'          => 42,
                'is_available'   => true,
                'emoji'          => '🦀',
                'gradient'       => 'linear-gradient(135deg,#FDE8D8,#F4C4A1)',
                'description'    => 'Rajungan segar diolah dengan bumbu pilihan, higienis dan lezat. Diproduksi oleh UMKM berpengalaman lebih dari 10 tahun di pesisir Cirebon. Cocok untuk oleh-oleh maupun konsumsi sehari-hari.',
            ],
            [
                'id'             => 2,
                'name'           => 'Ikan Garang Asap Pilihan',
                'category'       => 'Ikan Kering',
                'category_id'    => 'ikan',
                'store_name'     => 'Dapur Bahari Bu Siti',
                'store_location' => 'Pati, Jateng',
                'price'          => 45000,
                'unit'           => 'kg',
                'stock'          => 18,
                'is_available'   => true,
                'emoji'          => '🐟',
                'gradient'       => 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
                'description'    => 'Ikan garang pilihan diasapi tradisional dengan kayu mangrove pilihan. Proses pengasapan alami menghasilkan cita rasa khas yang gurih dan tahan lama. Tanpa bahan pengawet tambahan.',
            ],
            [
                'id'             => 3,
                'name'           => 'Kerupuk Ikan Tenggiri',
                'category'       => 'Kerupuk Ikan',
                'category_id'    => 'kerupuk',
                'store_name'     => 'UD Mitra Bahari',
                'store_location' => 'Rembang, Jateng',
                'price'          => 28000,
                'unit'           => 'kg',
                'stock'          => 150,
                'is_available'   => true,
                'emoji'          => '🌾',
                'gradient'       => 'linear-gradient(135deg,#FDF4CD,#F5E49A)',
                'description'    => 'Kerupuk ikan tenggiri renyah, tanpa pengawet, cocok untuk camilan keluarga. Dibuat dari ikan tenggiri segar pilihan dengan teknik pengolahan tradisional yang terjaga kualitasnya.',
            ],
            [
                'id'             => 4,
                'name'           => 'Udang Rebon Kering',
                'category'       => 'Udang Olahan',
                'category_id'    => 'udang',
                'store_name'     => 'Nelayan Sejahtera',
                'store_location' => 'Demak, Jateng',
                'price'          => 60000,
                'unit'           => 'kg',
                'stock'          => 5,
                'is_available'   => true,
                'emoji'          => '🦐',
                'gradient'       => 'linear-gradient(135deg,#FFE0D3,#FFB89A)',
                'description'    => 'Udang rebon segar dijemur alami, kaya protein dan mineral laut. Dipanen langsung dari perairan Demak oleh kelompok nelayan lokal. Ideal sebagai bumbu masakan atau lauk tambahan.',
            ],
            [
                'id'             => 5,
                'name'           => 'Cumi Asin Spesial',
                'category'       => 'Cumi-cumi',
                'category_id'    => 'cumi',
                'store_name'     => 'Usaha Pak Hasan',
                'store_location' => 'Cirebon, Jabar',
                'price'          => 72000,
                'unit'           => 'kg',
                'stock'          => 0,
                'is_available'   => false,
                'emoji'          => '🦑',
                'gradient'       => 'linear-gradient(135deg,#E8D5F5,#C9A8E8)',
                'description'    => 'Cumi-cumi asin berkualitas tinggi, dipilih dari tangkapan segar nelayan lokal. Diolah dengan metode penggaraman tradisional yang menghasilkan cita rasa autentik khas pesisir Cirebon.',
            ],
            [
                'id'             => 6,
                'name'           => 'Kepiting Bakau Olahan',
                'category'       => 'Kepiting',
                'category_id'    => 'kepiting',
                'store_name'     => 'Bahari Jaya Seafood',
                'store_location' => 'Brebes, Jateng',
                'price'          => 110000,
                'unit'           => 'kg',
                'stock'          => 8,
                'is_available'   => true,
                'emoji'          => '🦞',
                'gradient'       => 'linear-gradient(135deg,#D5EFD5,#9ED69E)',
                'description'    => 'Kepiting bakau segar dari tambak lokal, diolah dengan bumbu rahasia turun-temurun. Dagingnya padat, manis, dan gurih. Tersedia dalam kemasan vakum untuk menjaga kesegaran.',
            ],
        ];
    }

    private function getCategories(): array
    {
        return [
            ['id' => 'all',      'name' => 'Semua Produk',  'icon' => '🛒'],
            ['id' => 'rajungan', 'name' => 'Rajungan',       'icon' => '🦀'],
            ['id' => 'ikan',     'name' => 'Ikan Kering',    'icon' => '🐟'],
            ['id' => 'kerupuk',  'name' => 'Kerupuk Ikan',   'icon' => '🌾'],
            ['id' => 'udang',    'name' => 'Udang Olahan',   'icon' => '🦐'],
            ['id' => 'cumi',     'name' => 'Cumi-cumi',      'icon' => '🦑'],
            ['id' => 'kepiting', 'name' => 'Kepiting',       'icon' => '🦞'],
        ];
    }

    // ── GET /catalog ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $products       = $this->getProducts();
        $categories     = $this->getCategories();
        $activeCategory = $request->input('category_id', 'all');
        $search         = trim($request->input('search', ''));

        // Filter by category
        if ($activeCategory && $activeCategory !== 'all') {
            $products = array_values(array_filter(
                $products,
                fn($p) => $p['category_id'] === $activeCategory
            ));
        }

        // Filter by search keyword
        if ($search !== '') {
            $searchLower = strtolower($search);
            $products    = array_values(array_filter(
                $products,
                fn($p) =>
                    str_contains(strtolower($p['name']),      $searchLower) ||
                    str_contains(strtolower($p['category']),  $searchLower) ||
                    str_contains(strtolower($p['store_name']), $searchLower)
            ));
        }

        return view('catalog.index', compact('products', 'categories', 'activeCategory', 'search'));
    }

    // ── GET /catalog/product/{id} ─────────────────────────────────
    public function show(int $id)
    {
        $allProducts = $this->getProducts();

        $product = collect($allProducts)->firstWhere('id', $id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        return view('catalog.show', compact('product'));
    }
}
