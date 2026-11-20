<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\StoreMembership;

class OwnerController extends Controller
{
    private function getOwnerStore(): Store
    {
        return Store::firstOrCreate(
            ['name' => 'Usaha Pak Hasan'],
            [
                'location_label' => 'Cirebon, Jawa Barat',
                'is_active'      => true,
            ]
        );
    }

    /**
     * GET /owner/dashboard (Wireframe C)
     */
    public function dashboard()
    {
        $store = $this->getOwnerStore();

        $activeProductsCount = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->count();

        $incomingOrdersCount = Order::where('store_id', $store->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        $totalSales = Order::where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->sum('subtotal');

        $stats = [
            'active_products_count' => $activeProductsCount,
            'incoming_orders_count' => $incomingOrdersCount,
            'total_sales'           => (int) $totalSales,
        ];

        $orders = Order::with(['items', 'buyer'])
            ->where('store_id', $store->id)
            ->latest()
            ->take(8)
            ->get();

        $recentOrders = $orders->map(function ($o) {
            $firstItem = $o->items->first();
            return [
                'id'           => $o->id,
                'order_code'   => $o->order_code,
                'buyer_name'   => $o->recipient_name ?? ($o->buyer?->name ?? 'Pembeli'),
                'buyer_phone'  => $o->recipient_phone ?? '0812-3456-7890',
                'item_name'    => $firstItem?->product_name_snapshot ?? 'Produk Olahan Laut',
                'quantity'     => $firstItem?->quantity ?? 1,
                'unit'         => $firstItem?->unit_snapshot ?? 'kg',
                'total_amount' => $o->subtotal,
                'status'       => $o->status,
                'date'         => $o->created_at->format('d M Y, H:i'),
            ];
        });

        return view('owner.dashboard', compact('stats', 'recentOrders'));
    }

    /**
     * GET /owner/products (Daftar Produk Toko Lengkap)
     */
    public function products()
    {
        $store = $this->getOwnerStore();

        $products = Product::with('category')
            ->where('store_id', $store->id)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'category'    => $p->category?->name ?? 'Olahan Laut',
                    'price'       => $p->price,
                    'unit'        => $p->unit,
                    'stock'       => $p->stock_quantity,
                    'is_active'   => $p->is_active,
                    'emoji'       => $p->emoji ?? '🐟',
                    'gradient'    => $p->gradient ?? 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
                    'description' => $p->description,
                ];
            });

        return view('owner.products', compact('products'));
    }

    /**
     * POST /owner/products (Tambah Produk Baru ke Database)
     */
    public function storeProduct(Request $request)
    {
        $store = $this->getOwnerStore();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'unit'        => 'required|string|max:20',
            'price'       => 'required|integer|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $category = Category::where('name', $validated['category'])->first()
            ?? Category::first();

        $emojiMap = [
            'Rajungan'     => '🦀',
            'Ikan Kering'  => '🐟',
            'Kerupuk Ikan' => '🌾',
            'Udang Olahan' => '🦐',
            'Cumi-cumi'    => '🦑',
            'Kepiting'     => '🦞',
        ];

        $product = Product::create([
            'store_id'       => $store->id,
            'category_id'    => $category->id,
            'name'           => $validated['name'],
            'description'    => $validated['description'] ?? '',
            'price'          => $validated['price'],
            'unit'           => $validated['unit'] ?? 'kg',
            'stock_quantity' => $validated['stock'],
            'emoji'          => $emojiMap[$validated['category']] ?? '🐟',
            'gradient'       => 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
            'is_active'      => true,
        ]);

        return response()->json([
            'data'    => $product,
            'message' => "Produk '{$product->name}' berhasil ditambahkan ke database.",
        ], 201);
    }

    /**
     * PATCH /owner/products/{id} (Edit Produk)
     */
    public function updateProduct(Request $request, $id)
    {
        $store = $this->getOwnerStore();
        $product = Product::where('store_id', $store->id)->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|integer|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $product->update([
            'name'           => $validated['name'],
            'price'          => $validated['price'],
            'stock_quantity' => $validated['stock'],
            'description'    => $validated['description'] ?? '',
        ]);

        return response()->json([
            'data'    => $product,
            'message' => "Produk '{$product->name}' berhasil diperbarui.",
        ]);
    }

    /**
     * DELETE /owner/products/{id} (Soft Delete / Arsipkan Produk Sesuai Design System)
     */
    public function destroyProduct($id)
    {
        $store = $this->getOwnerStore();
        $product = Product::where('store_id', $store->id)->findOrFail($id);

        $name = $product->name;
        $product->delete(); // Soft delete

        return response()->json([
            'message' => "Produk '{$name}' berhasil dihapus dari katalog.",
        ]);
    }

    /**
     * GET /owner/orders (Daftar Pesanan Toko Lengkap)
     */
    public function orders(Request $request)
    {
        $store  = $this->getOwnerStore();
        $status = $request->input('status', 'all');

        $allOrders = Order::with(['items', 'buyer'])
            ->where('store_id', $store->id)
            ->latest()
            ->get()
            ->map(function ($o) {
                $firstItem = $o->items->first();
                return [
                    'id'           => $o->id,
                    'order_code'   => $o->order_code,
                    'buyer_name'   => $o->recipient_name ?? ($o->buyer?->name ?? 'Pembeli'),
                    'buyer_phone'  => $o->recipient_phone ?? '0812-3456-7890',
                    'item_name'    => $firstItem?->product_name_snapshot ?? 'Produk Olahan Laut',
                    'quantity'     => $firstItem?->quantity ?? 1,
                    'unit'         => $firstItem?->unit_snapshot ?? 'kg',
                    'total_amount' => $o->subtotal,
                    'status'       => $o->status,
                    'date'         => $o->created_at->format('d M Y, H:i'),
                ];
            });

        $counts = [
            'pending'    => $allOrders->where('status', 'pending')->count(),
            'processing' => $allOrders->where('status', 'processing')->count(),
            'shipped'    => $allOrders->where('status', 'shipped')->count(),
            'completed'  => $allOrders->where('status', 'completed')->count(),
        ];

        if ($status && $status !== 'all') {
            $filteredOrders = $allOrders->filter(fn($o) => $o['status'] === $status)->values();
        } else {
            $filteredOrders = $allOrders;
        }

        return view('owner.orders', [
            'orders'    => $filteredOrders,
            'allOrders' => $allOrders,
            'counts'    => $counts,
        ]);
    }

    /**
     * PATCH /owner/orders/{orderCode}/status (Update Status Pesanan)
     */
    public function updateOrderStatus(Request $request, $orderCode)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $order = Order::where('order_code', $orderCode)->firstOrFail();
        $order->status = $validated['status'];
        $order->save();

        return response()->json([
            'data'    => $order,
            'message' => "Status pesanan #{$order->order_code} berhasil diperbarui menjadi {$order->status}.",
        ]);
    }

    public function finance()
    {
        return $this->dashboard();
    }

    public function employees()
    {
        $store = $this->getOwnerStore();

        $memberships = StoreMembership::with('user')
            ->where('store_id', $store->id)
            ->where('role', 'employee')
            ->get();

        $employees = $memberships->map(function ($m) {
            return [
                'id'          => $m->id,
                'name'        => $m->user?->name ?? 'Pegawai',
                'email'       => $m->user?->email ?? '-',
                'role'        => 'Staf Operasional',
                'permissions' => $m->permissions ?? ['orders.read'],
                'status'      => ucfirst($m->status),
            ];
        });

        return view('owner.employees', compact('employees'));
    }
}
