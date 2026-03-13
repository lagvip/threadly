<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Role;
use App\Models\Size;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoAdminCustomerManagerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $adminRole = Role::updateOrCreate(
                ['slug' => 'admin'],
                [
                    'name' => 'admin',
                    'permissions' => null,
                ]
            );

            $managerRole = Role::updateOrCreate(
                ['id' => 2],
                [
                    'slug' => 'manager',
                    'name' => 'quản lý',
                    'permissions' => null,
                ]
            );

            $customerRole = Role::updateOrCreate(
                ['slug' => 'customer'],
                [
                    'name' => 'khách hàng',
                    'permissions' => null,
                ]
            );

            $admin = User::updateOrCreate(
                ['email' => 'vudinhminh199@gmail.com'],
                [
                    'name' => 'Vũ Đình Minh',
                    'password' => Hash::make('lagvip'),
                    'status' => 1,
                ]
            );

            $manager = User::updateOrCreate(
                ['email' => 'quanly@gmail.com'],
                [
                    'name' => 'Quản Lý Demo',
                    'password' => Hash::make('lagvip'),
                    'status' => 1,
                ]
            );

            $customer = User::updateOrCreate(
                ['email' => 'khachhang@gmail.com'],
                [
                    'name' => 'Khách Hàng Demo',
                    'password' => Hash::make('lagvip'),
                    'status' => 1,
                ]
            );

            DB::table('role_users')->updateOrInsert(
                ['user_id' => $admin->id, 'role_id' => $adminRole->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            DB::table('role_users')->updateOrInsert(
                ['user_id' => $manager->id, 'role_id' => $managerRole->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            DB::table('role_users')->updateOrInsert(
                ['user_id' => $customer->id, 'role_id' => $customerRole->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            $brand1 = Brand::updateOrCreate(
                ['name' => 'Nike Demo'],
                []
            );

            $brand2 = Brand::updateOrCreate(
                ['name' => 'Gucci Demo'],
                []
            );

            $category1 = Category::updateOrCreate(
                ['name' => 'Giày demo nam'],
                [
                    'id_parent' => null,
                    'image' => 'category/demo-category-1.jpg',
                ]
            );

            $category2 = Category::updateOrCreate(
                ['name' => 'Sneaker demo cao cấp'],
                [
                    'id_parent' => $category1->id,
                    'image' => 'category/demo-category-2.jpg',
                ]
            );

            $colorBlack = Color::updateOrCreate(
                ['name' => 'Đen'],
                [
                    'code' => '#000000',
                ]
            );

            $colorWhite = Color::updateOrCreate(
                ['name' => 'Trắng'],
                [
                    'code' => '#FFFFFF',
                ]
            );

            $size40 = Size::updateOrCreate(
                ['name' => '40'],
                []
            );

            $size41 = Size::updateOrCreate(
                ['name' => '41'],
                []
            );

            $product1 = Product::updateOrCreate(
                ['name' => 'Nike Air Max Demo'],
                [
                    'description' => 'Sản phẩm demo dùng để test đơn hàng và bình luận',
                    'id_brand' => $brand1->id,
                    'id_category' => $category1->id,
                    'image_primary' => 'products/nike-air-max-demo.jpg',
                    'status' => 'active',
                ]
            );

            $product2 = Product::updateOrCreate(
                ['name' => 'Gucci Ace Demo'],
                [
                    'description' => 'Sản phẩm demo thứ hai',
                    'id_brand' => $brand2->id,
                    'id_category' => $category2->id,
                    'image_primary' => 'products/gucci-ace-demo.jpg',
                    'status' => 'active',
                ]
            );

            $variant1 = ProductVariant::updateOrCreate(
                [
                    'id_product' => $product1->id,
                    'id_color' => $colorBlack->id,
                    'id_size' => $size40->id,
                ],
                [
                    'quantity' => 20,
                    'image' => 'variants/nike-air-max-black-40.jpg',
                    'price' => 570000,
                    'status' => 'active',
                ]
            );

            $variant2 = ProductVariant::updateOrCreate(
                [
                    'id_product' => $product2->id,
                    'id_color' => $colorWhite->id,
                    'id_size' => $size41->id,
                ],
                [
                    'quantity' => 15,
                    'image' => 'variants/gucci-ace-white-41.jpg',
                    'price' => 800000,
                    'status' => 'active',
                ]
            );

            $order1 = Order::updateOrCreate(
                ['order_code' => 'ORDDEMO001'],
                [
                    'user_id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => '0987654321',
                    'email' => $customer->email,
                    'address' => '12 Nguyễn Trãi, Thanh Xuân, Hà Nội',
                    'payment_method' => 'cod',
                    'payment_status' => 'unpaid',
                    'order_status' => 'pending',
                    'shipping_fee' => 30000,
                    'discount' => 10000,
                    'total_price' => 590000,
                    'previous_status' => null,
                    'cancel_reason' => null,
                ]
            );

            $order2 = Order::updateOrCreate(
                ['order_code' => 'ORDDEMO002'],
                [
                    'user_id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => '0987654321',
                    'email' => $customer->email,
                    'address' => '99 Lê Lợi, Quận 1, TP.HCM',
                    'payment_method' => 'vnpay',
                    'payment_status' => 'paid',
                    'order_status' => 'delivered',
                    'shipping_fee' => 25000,
                    'discount' => 15000,
                    'total_price' => 810000,
                    'previous_status' => 'shipped',
                    'cancel_reason' => null,
                ]
            );

            $order3 = Order::updateOrCreate(
                ['order_code' => 'ORDDEMO003'],
                [
                    'user_id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => '0987654321',
                    'email' => $customer->email,
                    'address' => 'Ký túc xá khu B, TP.HCM',
                    'payment_method' => 'cod',
                    'payment_status' => 'failed',
                    'order_status' => 'cancelled',
                    'shipping_fee' => 20000,
                    'discount' => 0,
                    'total_price' => 420000,
                    'previous_status' => 'pending',
                    'cancel_reason' => 'Khách đổi ý',
                ]
            );

            OrderDetail::updateOrCreate(
                [
                    'order_id' => $order1->id,
                    'product_id' => $product1->id,
                    'variant_id' => $variant1->id,
                ],
                [
                    'product_name' => $product1->name,
                    'quantity' => 1,
                    'unit_price' => 570000,
                    'total' => 570000,
                ]
            );

            OrderDetail::updateOrCreate(
                [
                    'order_id' => $order2->id,
                    'product_id' => $product2->id,
                    'variant_id' => $variant2->id,
                ],
                [
                    'product_name' => $product2->name,
                    'quantity' => 1,
                    'unit_price' => 800000,
                    'total' => 800000,
                ]
            );

            OrderDetail::updateOrCreate(
                [
                    'order_id' => $order3->id,
                    'product_id' => $product1->id,
                    'variant_id' => $variant1->id,
                ],
                [
                    'product_name' => $product1->name,
                    'quantity' => 1,
                    'unit_price' => 400000,
                    'total' => 400000,
                ]
            );

            Review::updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'product_id' => $product1->id,
                    'order_id' => $order1->id,
                ],
                [
                    'rating' => 5,
                    'comment' => 'Giày đẹp, đi êm, giao hàng nhanh.',
                    'admin_reply' => 'Cảm ơn bạn đã ủng hộ shop.',
                    'admin_id' => $admin->id,
                ]
            );

            Review::updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'product_id' => $product2->id,
                    'order_id' => $order2->id,
                ],
                [
                    'rating' => 4,
                    'comment' => 'Sản phẩm đẹp, đóng gói ổn, sẽ quay lại.',
                    'admin_reply' => 'Shop cảm ơn bạn, mong tiếp tục được phục vụ.',
                    'admin_id' => $manager->id,
                ]
            );

            Review::updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'product_id' => $product1->id,
                    'order_id' => $order3->id,
                ],
                [
                    'rating' => 2,
                    'comment' => 'Đơn bị lỗi thanh toán nên chưa nhận được hàng.',
                    'admin_reply' => 'Shop xin lỗi, bạn vui lòng đặt lại hoặc liên hệ hỗ trợ.',
                    'admin_id' => $admin->id,
                ]
            );
        });
    }
}
