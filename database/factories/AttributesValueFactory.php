<?php

namespace Database\Factories;

use App\Models\Attributes;
use App\Models\AttributesValue;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributesValue>
 */
class AttributesValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'attributes_id' => Attributes::factory(),
            'value' => mb_substr(fake()->word(), 0, 100),
            'min_value' => 0,
            'max_value' => 100,
        ];
    }
}
