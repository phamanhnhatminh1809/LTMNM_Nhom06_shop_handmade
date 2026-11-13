<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Túi xách',       'slug' => 'tui-xach',     'icon' => 'bi-bag-heart'],
            ['id' => 2, 'name' => 'Móc khóa',       'slug' => 'moc-khoa',     'icon' => 'bi-key'],
            ['id' => 3, 'name' => 'Trang sức',      'slug' => 'trang-suc',    'icon' => 'bi-gem'],
            ['id' => 4, 'name' => 'Gấu bông',       'slug' => 'gau-bong',     'icon' => 'bi-heart'],
            ['id' => 5, 'name' => 'Khăn len + Nón', 'slug' => 'khan-len',     'icon' => 'bi-snow'],
            ['id' => 6, 'name' => 'Hoa handmade',   'slug' => 'hoa-handmade', 'icon' => 'bi-flower1'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['id' => $cat['id']], $cat);
        }
    }
}