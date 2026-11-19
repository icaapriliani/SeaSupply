<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private function getOperationalOrders(): array
    {
        return [
            [
                'id'           => 101,
                'order_code'   => 'SS-001',
                'buyer_name'   => 'Budi Santoso',
                'buyer_phone'  => '0812-3456-7890',
                'item_name'    => 'Olahan Rajungan Premium',
                'quantity'     => 2,
                'unit'         => 'kg',
                'total_amount' => 170000,
                'status'       => 'pending',
                'date'         => '19 Nov 2026, 14:20',
            ],
            [
                'id'           => 102,
                'order_code'   => 'SS-002',
                'buyer_name'   => 'Siti Aminah',
                'buyer_phone'  => '0857-1234-5678',
                'item_name'    => 'Ikan Garang Asap Pilihan',
                'quantity'     => 3,
                'unit'         => 'kg',
                'total_amount' => 135000,
                'status'       => 'processing',
                'date'         => '19 Nov 2026, 11:05',
            ],
            [
                'id'           => 103,
                'order_code'   => 'SS-003',
                'buyer_name'   => 'Ahmad Fadil',
                'buyer_phone'  => '0878-9988-7766',
                'item_name'    => 'Kerupuk Ikan Tenggiri',
                'quantity'     => 5,
                'unit'         => 'kg',
                'total_amount' => 140000,
                'status'       => 'shipped',
                'date'         => '18 Nov 2026, 16:45',
            ],
        ];
    }

    /**
     * GET /employee/orders (Wireframe D)
     */
    public function orders(Request $request)
    {
        $allOrders = $this->getOperationalOrders();
        $status    = $request->input('status', 'all');

        $counts = [
            'pending'    => count(array_filter($allOrders, fn($o) => $o['status'] === 'pending')),
            'processing' => count(array_filter($allOrders, fn($o) => $o['status'] === 'processing')),
        ];

        if ($status && $status !== 'all') {
            $orders = array_values(array_filter($allOrders, fn($o) => $o['status'] === $status));
        } else {
            $orders = $allOrders;
        }

        return view('employee.orders', compact('orders', 'counts'));
    }

    /**
     * GET /employee/summary
     */
    public function summary()
    {
        $allOrders = $this->getOperationalOrders();
        $pendingCount    = count(array_filter($allOrders, fn($o) => $o['status'] === 'pending'));
        $processingCount = count(array_filter($allOrders, fn($o) => $o['status'] === 'processing'));
        $lowStockCount   = 2; // Cumi Asin (0) & Udang Rebon (5)

        return view('employee.summary', compact('pendingCount', 'processingCount', 'lowStockCount'));
    }

    /**
     * GET /employee/products
     */
    public function products()
    {
        $products = [
            ['id' => 1, 'name' => 'Olahan Rajungan Premium', 'category' => 'Rajungan', 'price' => 85000, 'unit' => 'kg', 'stock' => 42],
            ['id' => 2, 'name' => 'Ikan Garang Asap Pilihan', 'category' => 'Ikan Kering', 'price' => 45000, 'unit' => 'kg', 'stock' => 18],
            ['id' => 3, 'name' => 'Kerupuk Ikan Tenggiri', 'category' => 'Kerupuk Ikan', 'price' => 28000, 'unit' => 'kg', 'stock' => 150],
            ['id' => 4, 'name' => 'Udang Rebon Kering', 'category' => 'Udang Olahan', 'price' => 60000, 'unit' => 'kg', 'stock' => 5],
            ['id' => 5, 'name' => 'Cumi Asin Spesial', 'category' => 'Cumi-cumi', 'price' => 72000, 'unit' => 'kg', 'stock' => 0],
            ['id' => 6, 'name' => 'Kepiting Bakau Olahan', 'category' => 'Kepiting', 'price' => 110000, 'unit' => 'kg', 'stock' => 8],
        ];

        return view('employee.products', compact('products'));
    }
}
