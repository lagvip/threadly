<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Shipping; // Nhớ import Model Shipping

class ShippingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách dữ liệu mẫu thực tế
        $shippings = [
            ['provider_name' => 'Giao Hàng Nhanh (GHN)', 'price' => 30000, ],
            ['provider_name' => 'Giao Hàng Tiết Kiệm (GHTK)', 'price' => 25000, ],
            ['provider_name' => 'Viettel Post', 'price' => 28000, ],
            ['provider_name' => 'J&T Express', 'price' => 27000, ],
            ['provider_name' => 'Ninja Van', 'price' => 32000, ],
            ['provider_name' => 'GrabExpress (Hỏa tốc)', 'price' => 55000, ],
            ['provider_name' => 'Ahamove', 'price' => 45000, ],
            ['provider_name' => 'VNPost (Bưu điện)', 'price' => 22000, ], // Thử set trạng thái Ẩn
            ['provider_name' => 'Shopee Xpress', 'price' => 26000, ],
            ['provider_name' => 'Best Express', 'price' => 24000, ],
        ];

        foreach ($shippings as $item) {
            // Kiểm tra xem tên đã tồn tại chưa để tránh trùng lặp khi chạy nhiều lần
            if (!Shipping::where('provider_name', $item['provider_name'])->exists()) {
                Shipping::create($item);
            }
        }
    }
}
