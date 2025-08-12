<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create sample products
        $products = [
            [
                'name' => 'Laptop Computer',
                'description' => 'High-performance laptop with 16GB RAM and 512GB SSD',
                'stock' => 25,
                'price_cents' => 129999, // $1,299.99
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with precision tracking',
                'stock' => 100,
                'price_cents' => 2999, // $29.99
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB mechanical keyboard with Cherry MX switches',
                'stock' => 50,
                'price_cents' => 14999, // $149.99
            ],
            [
                'name' => '4K Monitor',
                'description' => '27-inch 4K Ultra HD monitor with HDR support',
                'stock' => 15,
                'price_cents' => 39999, // $399.99
            ],
            [
                'name' => 'USB-C Hub',
                'description' => '7-in-1 USB-C hub with HDMI, USB, and SD card slots',
                'stock' => 75,
                'price_cents' => 4999, // $49.99
            ],
            [
                'name' => 'Gaming Headset',
                'description' => '7.1 surround sound gaming headset with noise cancellation',
                'stock' => 30,
                'price_cents' => 8999, // $89.99
            ],
            [
                'name' => 'External SSD',
                'description' => '1TB external SSD with USB 3.2 Gen 2 interface',
                'stock' => 40,
                'price_cents' => 12999, // $129.99
            ],
            [
                'name' => 'Webcam',
                'description' => '1080p HD webcam with built-in microphone',
                'stock' => 60,
                'price_cents' => 5999, // $59.99
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Test user created: test@example.com / password123');
        $this->command->info('Sample products created: ' . count($products));
    }
}
