<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attributes;
use App\Models\Product;
use App\Models\AttributesValue;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
        ]);

        // Create attributes
        $attributes = Attributes::factory(5)->create();

        // Create products with attribute values
        Product::factory(15)->create()->each(function (Product $product) use ($attributes) {
            // Randomly attach 2-4 attributes to each product
            $randomAttributes = $attributes->random(rand(2, 4));
            
            foreach ($randomAttributes as $attribute) {
                AttributesValue::factory()->create([
                    'product_id' => $product->product_id,
                    'attributes_id' => $attribute->attributes_id,
                ]);
            }
        });

        // Create orders with items for some users
        $users = User::all();
        $products = Product::all();

        foreach ($users->random(5) as $user) {
            Order::factory(2)->create([
                'uid' => $user->uid,
            ])->each(function (Order $order) use ($products) {
                // Add 2-5 unique items to each order (each product only once per order)
                $orderProducts = $products->random(rand(2, 5));
                
                foreach ($orderProducts as $product) {
                    OrderItem::factory()->create([
                        'order_id' => $order->order_id,
                        'product_id' => $product->product_id,
                    ]);
                }
            });
        }
    }
}
