<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
     * GET /cart (Halaman Keranjang Belanja Pembeli)
     */
    public function cart()
    {
        $productsMap = Product::with(['store', 'category'])
            ->where('is_active', true)
            ->get()
            ->keyBy('id')
            ->map(function ($p) {
                return [
                    'id'             => $p->id,
                    'name'           => $p->name,
                    'category'       => $p->category?->name ?? 'Olahan Laut',
                    'store_name'     => $p->store?->name ?? 'Usaha Pak Hasan',
                    'store_location' => $p->store?->location_label ?? 'Cirebon, Jawa Barat',
                    'price'          => $p->price,
                    'unit'           => $p->unit,
                    'stock'          => $p->stock_quantity,
                    'emoji'          => $p->emoji ?? '🐟',
                    'gradient'       => $p->gradient ?? 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
                ];
            });

        return view('catalog.cart', compact('productsMap'));
    }

    /**
     * POST /catalog/order (Pembuatan Pesanan Transaksional dengan Row Locking)
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1',
            'recipient_name'   => 'required|string|max:100',
            'recipient_phone'  => 'required|string|max:30',
            'delivery_address' => 'required|string|max:500',
            'buyer_note'       => 'nullable|string|max:500',
        ], [
            'recipient_name.required'   => 'Nama penerima pesanan wajib diisi.',
            'recipient_phone.required'  => 'Nomor telepon / WhatsApp penerima wajib diisi.',
            'delivery_address.required' => 'Alamat pengiriman lengkap wajib diisi.',
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

            // Cari atau buat default buyer (utamakan yang sedang login)
            $buyer = Auth::user() ?? User::first();

            // Simpan pesanan
            $order = Order::create([
                'order_code'      => $orderCode,
                'store_id'        => $product->store_id,
                'buyer_user_id'   => Auth::id() ?? $buyer?->id,
                'status'          => 'pending',
                'subtotal'        => $subtotal,
                'recipient_name'  => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'],
                'delivery_address'=> $validated['delivery_address'],
                'buyer_note'      => $validated['buyer_note'] ?? 'Pesanan via katalog online SeaSupply',
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

    /**
     * GET /catalog/orders (Riwayat Pesanan Pembeli)
     */
    public function orders(Request $request)
    {
        $status = $request->input('status', 'all');
        $userId = Auth::id();

        $query = Order::with(['items', 'store']);
        if ($userId) {
            $query->where('buyer_user_id', $userId);
        }

        $allOrders = $query->latest()->get()->map(function ($o) {
            $firstItem = $o->items->first();
            return [
                'id'             => $o->id,
                'order_code'     => $o->order_code,
                'store_name'     => $o->store?->name ?? 'UMKM Bahari',
                'store_location' => $o->store?->location_label ?? 'Indonesia',
                'item_name'      => $firstItem?->product_name_snapshot ?? 'Produk Olahan Laut',
                'unit_price'     => $firstItem?->unit_price_snapshot ?? 0,
                'unit'           => $firstItem?->unit_snapshot ?? 'kg',
                'quantity'       => $firstItem?->quantity ?? 1,
                'subtotal'       => $o->subtotal,
                'status'         => $o->status,
                'recipient_name' => $o->recipient_name,
                'recipient_phone'=> $o->recipient_phone,
                'delivery_address' => $o->delivery_address,
                'buyer_note'       => $o->buyer_note,
                'date'           => $o->created_at->format('d M Y, H:i'),
            ];
        });

        if ($status && $status !== 'all') {
            $filteredOrders = $allOrders->filter(fn($o) => $o['status'] === $status)->values();
        } else {
            $filteredOrders = $allOrders;
        }

        return view('catalog.orders', [
            'orders'    => $filteredOrders,
            'allOrders' => $allOrders,
        ]);
    }

    // =========================================================================
    // Upload Bukti Pembayaran
    // =========================================================================

    /**
     * POST /catalog/orders/{order}/upload-payment
     * Pembeli mengunggah foto bukti transfer
     */
    public function uploadPaymentProof(Request $request, Order $order)
    {
        // Pastikan order milik pembeli yang login
        if ($order->buyer_user_id !== Auth::id()) {
            return response()->json([
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Akses ditolak.'],
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'error' => ['code' => 'INVALID_STATUS', 'message' => 'Bukti pembayaran hanya bisa diunggah untuk pesanan berstatus Menunggu Pembayaran.'],
            ], 422);
        }

        $request->validate([
            'proof_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Hapus file lama jika ada
        if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = $request->file('proof_image')->store('payments', 'public');

        $order->payment_proof = $path;
        $order->save();

        return response()->json([
            'data'    => ['payment_proof_url' => Storage::url($path)],
            'message' => 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi toko.',
        ]);
    }
}
