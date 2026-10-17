<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['id' => 1], ['name' => Role::ADMIN]);
        Role::firstOrCreate(['id' => 2], ['name' => Role::CUSTOMER]);
        Role::firstOrCreate(['id' => 3], ['name' => Role::WAREHOUSE_STAFF]);
        Role::firstOrCreate(['id' => 4], ['name' => Role::SALES_STAFF]);
    }
}
