<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default Seeded Users
        User::updateOrCreate(
            ['email' => 'admin@textilepos.com'],
            [
                'name' => 'Store Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'cashier@textilepos.com'],
            [
                'name' => 'Cashier One',
                'password' => Hash::make('password'),
                'role' => 'cashier',
            ]
        );

        // Categories
        $categories = [
            'Denims' => 'denims',
            'Frocks' => 'frocks',
            'T-Shirts & Tops' => 't-shirts-tops',
            'Sarees & Ethnic' => 'sarees-ethnic',
            'Jackets & Coats' => 'jackets-coats',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $slug) {
            $categoryModels[$name] = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        // Textile Products with Item Codes / Barcodes
        $products = [
            // Denims
            [
                'category' => 'Denims',
                'name' => 'Slim Fit Dark Blue Denim Jeans',
                'item_code' => 'DNM-001',
                'price' => 4500.00,
                'stock_qty' => 25,
            ],
            [
                'category' => 'Denims',
                'name' => 'Classic Regular Fit Light Blue Denim',
                'item_code' => 'DNM-002',
                'price' => 4200.00,
                'stock_qty' => 18,
            ],
            [
                'category' => 'Denims',
                'name' => 'High-Waist Skinny Stretch Denim',
                'item_code' => 'DNM-003',
                'price' => 4800.00,
                'stock_qty' => 12,
            ],

            // Frocks
            [
                'category' => 'Frocks',
                'name' => 'Floral Summer Max Frock',
                'item_code' => 'FRK-001',
                'price' => 3800.00,
                'stock_qty' => 20,
            ],
            [
                'category' => 'Frocks',
                'name' => 'Elegant Velvet Evening Party Frock',
                'item_code' => 'FRK-002',
                'price' => 6500.00,
                'stock_qty' => 8,
            ],
            [
                'category' => 'Frocks',
                'name' => 'Casual Cotton A-Line Frock',
                'item_code' => 'FRK-003',
                'price' => 2900.00,
                'stock_qty' => 30,
            ],

            // T-Shirts & Tops
            [
                'category' => 'T-Shirts & Tops',
                'name' => 'Premium Crewneck Cotton T-Shirt',
                'item_code' => 'TSH-001',
                'price' => 1800.00,
                'stock_qty' => 50,
            ],
            [
                'category' => 'T-Shirts & Tops',
                'name' => 'Oversized Streetwear Graphic Tee',
                'item_code' => 'TSH-002',
                'price' => 2200.00,
                'stock_qty' => 40,
            ],

            // Sarees & Ethnic
            [
                'category' => 'Sarees & Ethnic',
                'name' => 'Kanchipuram Silk Saree - Royal Blue',
                'item_code' => 'SAR-001',
                'price' => 12500.00,
                'stock_qty' => 5,
            ],
            [
                'category' => 'Sarees & Ethnic',
                'name' => 'Printed Georgette Daily Wear Saree',
                'item_code' => 'SAR-002',
                'price' => 3500.00,
                'stock_qty' => 15,
            ],

            // Jackets
            [
                'category' => 'Jackets & Coats',
                'name' => 'Biker Style Black Leatherette Jacket',
                'item_code' => 'JCK-001',
                'price' => 7900.00,
                'stock_qty' => 10,
            ],
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(
                ['item_code' => $prod['item_code']],
                [
                    'category_id' => $categoryModels[$prod['category']]->id,
                    'name' => $prod['name'],
                    'price' => $prod['price'],
                    'stock_qty' => $prod['stock_qty'],
                ]
            );
        }
    }
}
