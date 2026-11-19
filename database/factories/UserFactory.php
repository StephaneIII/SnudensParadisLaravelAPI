<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => mb_substr(fake()->firstName(), 0, 50),
            'last_name' => mb_substr(fake()->lastName(), 0, 50),
            'email' => fake()->unique()->numerify('user##########').'@example.test',
            'password' => static::$password ??= Hash::make('password'),
            'tlf' => fake()->numerify('########'),
            'is_employee' => false,
        ];
    }
}
