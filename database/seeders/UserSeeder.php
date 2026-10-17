<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@shophandmade.com'],
            [
                'id' => 1,
                'role_id' => 1,
                'username' => 'shop_admin',
                'password' => Hash::make('password123'),
                'full_name' => 'Quản trị viên',
                'phone' => '0901234567',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'test_customer@gmail.com'],
            [
                'id' => 2,
                'role_id' => 2,
                'username' => 'test_customer',
                'password' => Hash::make('password123'),
                'full_name' => 'Nguyễn Văn An',
                'phone' => '0912345678',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'tranbinh@mail.com'],
            [
                'id' => 3,
                'role_id' => 2,
                'username' => 'tranbinh',
                'password' => Hash::make('password123'),
                'full_name' => 'Trần Thị Bình',
                'phone' => '0923456789',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'levanminh@gmail.com'],
            [
                'id' => 4,
                'role_id' => 2,
                'username' => 'levanminh',
                'password' => Hash::make('password123'),
                'full_name' => 'Lê Văn Minh',
                'phone' => '0934567890',
                'is_active' => true,
            ]
        );

        // Tài khoản Nhân viên kho mẫu
        User::firstOrCreate(
            ['email' => 'warehouse@shophandmade.com'],
            [
                'role_id' => 3,
                'username' => 'staff_warehouse',
                'password' => Hash::make('password123'),
                'full_name' => 'Phạm Hoàng Kho',
                'phone' => '0945678901',
                'is_active' => true,
            ]
        );

        // Tài khoản Nhân viên bán hàng mẫu
        User::firstOrCreate(
            ['email' => 'sales@shophandmade.com'],
            [
                'role_id' => 4,
                'username' => 'staff_sales',
                'password' => Hash::make('password123'),
                'full_name' => 'Đỗ Thu Bán Hàng',
                'phone' => '0956789012',
                'is_active' => true,
            ]
        );
    }
}
