<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Order;
use App\Models\Product;

class EmployeeController extends Controller
{
    private function getStore(): Store
    {
        return Store::firstOrCreate(['name' => 'Usaha Pak Hasan']);
    }

    /**
     * GET /employee/orders (Wireframe D)
     */
    public function orders(Request $request)
    {
        $store  = $this->getStore();
        $status = $request->input('status', 'all');

        $query = Order::with(['items', 'buyer'])
            ->where('store_id', $store->id);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $allOrders = $query->latest()->get()->map(function ($o) {
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
            'pending'    => Order::where('store_id', $store->id)->where('status', 'pending')->count(),
            'processing' => Order::where('store_id', $store->id)->where('status', 'processing')->count(),
        ];

        return view('employee.orders', [
            'orders' => $allOrders,
            'counts' => $counts,
        ]);
    }

    /**
     * PATCH /employee/orders/{orderCode}/status
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

    /**
     * GET /employee/summary
     */
    public function summary()
    {
        $store = $this->getStore();

        $pendingCount    = Order::where('store_id', $store->id)->where('status', 'pending')->count();
        $processingCount = Order::where('store_id', $store->id)->where('status', 'processing')->count();
        $lowStockCount   = Product::where('store_id', $store->id)->where('stock_quantity', '<=', 10)->count();

        return view('employee.summary', compact('pendingCount', 'processingCount', 'lowStockCount'));
    }

    /**
     * GET /employee/products
     */
    public function products()
    {
        $store = $this->getStore();

        $products = Product::with('category')
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->get()
            ->map(function ($p) {
                return [
                    'id'       => $p->id,
                    'name'     => $p->name,
                    'category' => $p->category?->name ?? 'Olahan Laut',
                    'price'    => $p->price,
                    'unit'     => $p->unit,
                    'stock'    => $p->stock_quantity,
                ];
            });

        return view('employee.products', compact('products'));
    }
}
