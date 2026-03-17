<?php

namespace Database\Factories;

use App\Models\PositionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PositionType>
 */
class PositionTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'name' => fake()->unique()->words(1, true),
            'is_active' => fake()->boolean(90),
        ];
    }
}
