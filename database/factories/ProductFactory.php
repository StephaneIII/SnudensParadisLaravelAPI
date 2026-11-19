<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => mb_substr(fake()->words(3, true), 0, 30),
            'rating' => fake()->randomFloat(2, 0, 5),
            'price' => fake()->randomFloat(2, 1, 1000),
            'description' => mb_substr(fake()->sentence(), 0, 150),
            'mærke' => mb_substr(fake()->company(), 0, 100),
        ];
    }
}
