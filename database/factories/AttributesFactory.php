<?php

namespace Database\Factories;

use App\Models\Attributes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attributes>
 */
class AttributesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attributes_name' => mb_substr(fake()->word(), 0, 100),
            'description' => mb_substr(fake()->sentence(), 0, 100),
            'data_type' => 'string',
        ];
    }
}
