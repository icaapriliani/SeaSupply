<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OwnerController extends Controller
{
    /**
     * Data mock toko Owner (Usaha Pak Hasan)
     */
    private function getOwnerStats(): array
    {
        return [
            'active_products_count' => 6,
            'incoming_orders_count' => 3,
            'total_sales'           => 1420000,
        ];
    }

    private function getRecentOrders(): array
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
            [
                'id'           => 104,
                'order_code'   => 'SS-004',
                'buyer_name'   => 'Dewi Lestari',
                'buyer_phone'  => '0813-4455-6677',
                'item_name'    => 'Kepiting Bakau Olahan',
                'quantity'     => 1,
                'unit'         => 'kg',
                'total_amount' => 110000,
                'status'       => 'completed',
                'date'         => '17 Nov 2026, 09:30',
            ],
        ];
    }

    /**
     * GET /owner/dashboard (Wireframe C)
     */
    public function dashboard()
    {
        $stats        = $this->getOwnerStats();
        $recentOrders = $this->getRecentOrders();

        return view('owner.dashboard', compact('stats', 'recentOrders'));
    }

    /**
     * GET /owner/products
     */
    public function products()
    {
        $products = [
            ['id' => 1, 'name' => 'Olahan Rajungan Premium', 'category' => 'Rajungan', 'price' => 85000, 'unit' => 'kg', 'stock' => 42, 'is_active' => true],
            ['id' => 2, 'name' => 'Ikan Garang Asap Pilihan', 'category' => 'Ikan Kering', 'price' => 45000, 'unit' => 'kg', 'stock' => 18, 'is_active' => true],
            ['id' => 3, 'name' => 'Kerupuk Ikan Tenggiri', 'category' => 'Kerupuk Ikan', 'price' => 28000, 'unit' => 'kg', 'stock' => 150, 'is_active' => true],
            ['id' => 4, 'name' => 'Udang Rebon Kering', 'category' => 'Udang Olahan', 'price' => 60000, 'unit' => 'kg', 'stock' => 5, 'is_active' => true],
            ['id' => 5, 'name' => 'Cumi Asin Spesial', 'category' => 'Cumi-cumi', 'price' => 72000, 'unit' => 'kg', 'stock' => 0, 'is_active' => false],
            ['id' => 6, 'name' => 'Kepiting Bakau Olahan', 'category' => 'Kepiting', 'price' => 110000, 'unit' => 'kg', 'stock' => 8, 'is_active' => true],
        ];

        return view('owner.dashboard', [
            'stats'        => $this->getOwnerStats(),
            'recentOrders' => $this->getRecentOrders(),
        ]);
    }

    /**
     * GET /owner/orders
     */
    public function orders()
    {
        return view('owner.dashboard', [
            'stats'        => $this->getOwnerStats(),
            'recentOrders' => $this->getRecentOrders(),
        ]);
    }

    /**
     * GET /owner/finance
     */
    public function finance()
    {
        return view('owner.dashboard', [
            'stats'        => $this->getOwnerStats(),
            'recentOrders' => $this->getRecentOrders(),
        ]);
    }

    /**
     * GET /owner/employees (Atur Pegawai)
     */
    public function employees()
    {
        return view('owner.employees', [
            'employees' => [
                ['id' => 1, 'name' => 'Rian Hidayat', 'email' => 'rian@seasupply.local', 'role' => 'Staf Operasional', 'permissions' => ['orders.read', 'orders.update_status'], 'status' => 'Aktif'],
                ['id' => 2, 'name' => 'Nurul Aini', 'email' => 'nurul@seasupply.local', 'role' => 'Staf Gudang', 'permissions' => ['products.read'], 'status' => 'Aktif'],
            ]
        ]);
    }
}
