<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\User;

class CatalogController extends Controller
{
    /**
     * GET /catalog (Wireframe A — Katalog Pembeli)
     */
    public function index(Request $request)
    {
        $categories     = Category::where('is_active', true)->get();
        $activeCategory = $request->input('category_id', 'all');
        $search         = trim($request->input('search', ''));

        $query = Product::with(['store', 'category'])
            ->where('is_active', true);

        // Filter kategori
        if ($activeCategory && $activeCategory !== 'all') {
            $query->whereHas('category', function ($q) use ($activeCategory) {
                $q->where('slug', $activeCategory)
                  ->orWhere('id', $activeCategory);
            });
        }

        // Filter kata kunci pencarian
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('store', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $products = $query->orderBy('id', 'asc')->get()->map(function ($p) {
            return [
                'id'             => $p->id,
                'name'           => $p->name,
                'category'       => $p->category?->name ?? 'Olahan Laut',
                'category_id'    => $p->category?->slug ?? 'all',
                'store_name'     => $p->store?->name ?? 'UMKM Bahari',
                'store_location' => $p->store?->location_label ?? 'Indonesia',
                'price'          => $p->price,
                'unit'           => $p->unit,
                'stock'          => $p->stock_quantity,
                'is_available'   => $p->is_available,
                'emoji'          => $p->emoji ?? '🐟',
                'gradient'       => $p->gradient ?? 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
                'description'    => $p->description,
            ];
        });

        // Format kategori untuk UI chip (tambahkan chip 'all')
        $categoryChips = collect([
            ['id' => 'all', 'slug' => 'all', 'name' => 'Semua Produk', 'icon' => '🛒']
        ])->concat($categories->map(function ($c) {
            return [
                'id'   => $c->slug,
                'slug' => $c->slug,
                'name' => $c->name,
                'icon' => $c->icon ?? '🌊',
            ];
        }))->values()->all();

        return view('catalog.index', [
            'products'       => $products,
            'categories'     => $categoryChips,
            'activeCategory' => $activeCategory,
            'search'         => $search,
        ]);
    }

    /**
     * GET /catalog/product/{id} (Wireframe B — Detail Produk & Pemesanan)
     */
    public function show($id)
    {
        $p = Product::with(['store', 'category'])
            ->where('is_active', true)
            ->findOrFail($id);

        $product = [
            'id'             => $p->id,
            'name'           => $p->name,
            'category'       => $p->category?->name ?? 'Olahan Laut',
            'category_id'    => $p->category?->slug ?? 'all',
            'store_name'     => $p->store?->name ?? 'UMKM Bahari',
            'store_location' => $p->store?->location_label ?? 'Indonesia',
            'price'          => $p->price,
            'unit'           => $p->unit,
            'stock'          => $p->stock_quantity,
            'is_available'   => $p->is_available,
            'emoji'          => $p->emoji ?? '🐟',
            'gradient'       => $p->gradient ?? 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
            'description'    => $p->description,
        ];

        return view('catalog.show', compact('product'));
    }

    /**
     * POST /catalog/order (Pembuatan Pesanan Transaksional dengan Row Locking)
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'recipient_name'  => 'nullable|string|max:100',
            'recipient_phone' => 'nullable|string|max:30',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Pessimistic locking: kunci baris produk untuk mencegah race condition
            $product = Product::lockForUpdate()->find($validated['product_id']);

            if (!$product || !$product->is_active) {
                return response()->json([
                    'error' => [
                        'code'    => 'PRODUCT_UNAVAILABLE',
                        'message' => 'Produk ini sedang tidak aktif atau tidak tersedia.',
                    ]
                ], 400);
            }

            if ($product->stock_quantity < $validated['quantity']) {
                return response()->json([
                    'error' => [
                        'code'    => 'INSUFFICIENT_STOCK',
                        'message' => 'Stok produk tidak mencukupi untuk jumlah yang diminta.',
                        'details' => [
                            'product_id'         => $product->id,
                            'requested_quantity' => (int) $validated['quantity'],
                            'available_quantity' => (int) $product->stock_quantity,
                        ]
                    ]
                ], 409);
            }

            // Kurangi stok produk secara atomik
            $product->stock_quantity -= $validated['quantity'];
            $product->save();

            // Hitung subtotal di backend (harga dari DB, bukan client)
            $subtotal = $product->price * $validated['quantity'];

            // Generate kode pesanan unik
            $orderCode = 'SS-' . str_pad((string) (Order::max('id') + 1), 3, '0', STR_PAD_LEFT);

            // Cari atau buat default buyer
            $buyer = User::first();

            // Simpan pesanan
            $order = Order::create([
                'order_code'      => $orderCode,
                'store_id'        => $product->store_id,
                'buyer_user_id'   => $buyer?->id,
                'status'          => 'pending',
                'subtotal'        => $subtotal,
                'recipient_name'  => $validated['recipient_name'] ?? ($buyer?->name ?? 'Pembeli'),
                'recipient_phone' => $validated['recipient_phone'] ?? '0812-3456-7890',
                'delivery_address'=> 'Alamat pembeli',
                'buyer_note'      => 'Pesanan via katalog online SeaSupply',
            ]);

            // Simpan snapshot item pesanan
            OrderItem::create([
                'order_id'              => $order->id,
                'product_id'            => $product->id,
                'product_name_snapshot' => $product->name,
                'unit_price_snapshot'   => $product->price,
                'unit_snapshot'         => $product->unit,
                'quantity'              => $validated['quantity'],
                'line_total'            => $subtotal,
            ]);

            return response()->json([
                'data' => [
                    'order_id'   => $order->id,
                    'order_code' => $order->order_code,
                    'status'     => $order->status,
                    'subtotal'   => $order->subtotal,
                    'remaining_stock' => $product->stock_quantity,
                ],
                'message' => 'Pesanan berhasil dibuat dan tersimpan di database.',
            ], 201);
        });
    }
}
