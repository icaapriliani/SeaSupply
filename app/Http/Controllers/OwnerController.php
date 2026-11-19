<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\StoreMembership;
use App\Models\User;

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

    public function products()
    {
        return $this->dashboard();
    }

    public function orders()
    {
        return $this->dashboard();
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
