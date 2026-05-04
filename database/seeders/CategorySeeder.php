<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Camisetas',  'slug' => 'camisetas',  'description' => 'Camisetas masculinas e femininas'],
            ['name' => 'Calças',     'slug' => 'calcas',     'description' => 'Calças jeans e tecido'],
            ['name' => 'Acessórios', 'slug' => 'acessorios', 'description' => 'Bonés, cintos e bolsas'],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(['slug' => $data['slug']], array_merge($data, ['active' => true]));
        }
    }
}
