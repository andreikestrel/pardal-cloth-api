<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $camisetas  = Category::where('slug', 'camisetas')->first();
        $calcas     = Category::where('slug', 'calcas')->first();
        $acessorios = Category::where('slug', 'acessorios')->first();

        $products = [
            [
                'category'    => $camisetas,
                'name'        => 'Camiseta Básica',
                'slug'        => 'camiseta-basica',
                'description' => 'Camiseta 100% algodão, corte regular.',
                'base_price'  => '59.90',
                'variations'  => [
                    ['size' => 'P',  'color' => 'Branco', 'sku' => 'CAM-BAS-P-BCO',  'price' => '59.90', 'stock' => 20, 'min_stock' => 5],
                    ['size' => 'M',  'color' => 'Branco', 'sku' => 'CAM-BAS-M-BCO',  'price' => '59.90', 'stock' => 25, 'min_stock' => 5],
                    ['size' => 'G',  'color' => 'Branco', 'sku' => 'CAM-BAS-G-BCO',  'price' => '59.90', 'stock' => 15, 'min_stock' => 5],
                    ['size' => 'P',  'color' => 'Preto',  'sku' => 'CAM-BAS-P-PTO',  'price' => '59.90', 'stock' => 18, 'min_stock' => 5],
                    ['size' => 'M',  'color' => 'Preto',  'sku' => 'CAM-BAS-M-PTO',  'price' => '59.90', 'stock' => 22, 'min_stock' => 5],
                    ['size' => 'G',  'color' => 'Preto',  'sku' => 'CAM-BAS-G-PTO',  'price' => '59.90', 'stock' => 3,  'min_stock' => 5],
                ],
            ],
            [
                'category'    => $calcas,
                'name'        => 'Calça Jeans Slim',
                'slug'        => 'calca-jeans-slim',
                'description' => 'Calça jeans masculina corte slim.',
                'base_price'  => '149.90',
                'variations'  => [
                    ['size' => '38', 'color' => 'Azul', 'sku' => 'CAL-JNS-38-AZL', 'price' => '149.90', 'stock' => 10, 'min_stock' => 3],
                    ['size' => '40', 'color' => 'Azul', 'sku' => 'CAL-JNS-40-AZL', 'price' => '149.90', 'stock' => 12, 'min_stock' => 3],
                    ['size' => '42', 'color' => 'Azul', 'sku' => 'CAL-JNS-42-AZL', 'price' => '149.90', 'stock' => 8,  'min_stock' => 3],
                    ['size' => '38', 'color' => 'Preto', 'sku' => 'CAL-JNS-38-PTO', 'price' => '149.90', 'stock' => 2,  'min_stock' => 3],
                    ['size' => '40', 'color' => 'Preto', 'sku' => 'CAL-JNS-40-PTO', 'price' => '149.90', 'stock' => 7,  'min_stock' => 3],
                ],
            ],
            [
                'category'    => $acessorios,
                'name'        => 'Boné Pardal',
                'slug'        => 'bone-pardal',
                'description' => 'Boné aba curva com bordado Pardal.',
                'base_price'  => '79.90',
                'variations'  => [
                    ['size' => 'Único', 'color' => 'Preto',  'sku' => 'BON-PAR-UN-PTO', 'price' => '79.90', 'stock' => 30, 'min_stock' => 5],
                    ['size' => 'Único', 'color' => 'Branco', 'sku' => 'BON-PAR-UN-BCO', 'price' => '79.90', 'stock' => 25, 'min_stock' => 5],
                    ['size' => 'Único', 'color' => 'Cáqui',  'sku' => 'BON-PAR-UN-CAQ', 'price' => '79.90', 'stock' => 4,  'min_stock' => 5],
                ],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $data['category']?->id,
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'base_price'  => $data['base_price'],
                    'active'      => true,
                ]
            );

            if ($product->wasRecentlyCreated) {
                foreach ($data['variations'] as $v) {
                    $product->variations()->create($v);
                }
            }
        }
    }
}
