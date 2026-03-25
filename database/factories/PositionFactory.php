<?php

namespace Database\Factories;

use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Position;
use App\Models\VacationPolicy;
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
            'place_id' => Place::inRandomOrder()->first()->id,
            'employee_id' => Employee::inRandomOrder()->first()->id,
            'department_id' => Department::inRandomOrder()->first()->id,
            'position_type' => fake()->randomElement(PositionType::cases()),
            'date_start' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'date_end' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => fake()->randomElement(PositionStatus::cases()),
            'act_number' => fake()->numberBetween(1, 1000),
            'act_date' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'staff_type' => fake()->boolean(),
            'clinical' => fake()->boolean(),
            'clinical_text' => fake()->optional(0.7)->realText(200),
            'automative_renewal' => fake()->boolean(),
            'salary' => fake()->numberBetween(1000, 10000),
            'vacation_policy_id' => VacationPolicy::inRandomOrder()->first()->id,
            'comment' => fake()->optional(0.7)->realText(200),
        ];
    }
}
