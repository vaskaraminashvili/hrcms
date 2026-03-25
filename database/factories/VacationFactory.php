<?php

namespace Database\Factories;

use App\Enums\VacationStatus;
use App\Enums\VacationType;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Vacation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacation>
 */
class VacationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'position_id' => Position::factory(),
            'start_date' => fake()->dateTimeBetween('-10 weeks', '-1 week'),
            'end_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'working_days_count' => fake()->numberBetween(0, 5),
            'type' => fake()->randomElement(VacationType::cases()),
            'status' => fake()->randomElement(VacationStatus::cases()),
            'reason' => fake()->optional(0.7)->realText(200),
            'notes' => fake()->optional(0.7)->realText(200),
        ];
    }
}
