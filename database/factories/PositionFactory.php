<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::inRandomOrder()->first()->id,
            'date_start' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'date_end' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => fake()->randomElement(['active', 'inactive']),
            'act_number' => fake()->numberBetween(1, 1000),
            'act_date' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'automative_renewal' => fake()->boolean(),
            'salary' => fake()->numberBetween(1000, 10000),
            'comment' => fake()->optional(0.7)->realText(200),
            'department_id' => Department::inRandomOrder()->first()->id,
        ];
    }
}
