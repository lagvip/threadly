<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // 1. Tạo Role Admin (quan trọng nhất là slug phải là 'admin')
    $adminRole = \App\Models\Role::create([
        'name' => 'Administrator',
        'slug' => 'admin',
        'permissions' => json_encode(['all' => true]),
    ]);

    // 2. Tạo User Admin
    $user = \App\Models\User::create([
        'name' => 'Admin Threadly',
        'email' => 'admin@gmail.com',
        'password' => bcrypt('password'), // Mật khẩu là: password
        'status' => 1, // Dạng số như lỗi trước đó đã fix
        'email_verified_at' => now(),
    ]);

    // 3. Gán Role cho User (Giả sử bạn dùng bảng trung gian role_users)
    // Nếu bạn dùng thư viện Sentinel hoặc tự viết relation:
    $user->roles()->attach($adminRole->id); 
}
    
    
}
