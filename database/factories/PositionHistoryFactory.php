<?php

namespace Database\Factories;

use App\Enums\PositionStatus;
use App\Models\Position;
use App\Models\PositionHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PositionHistory>
 */
class PositionHistoryFactory extends Factory
{
    protected $model = PositionHistory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position_id' => Position::factory(),
            'changed_by' => User::query()->inRandomOrder()->value('id'),
            'event_type' => fake()->randomElement(['created', 'updated', 'deleted']),
            'snapshot' => [
                'salary' => fake()->numberBetween(1000, 10000),
                'status' => fake()->randomElement(PositionStatus::cases())->value,
                'employee_id' => fake()->numberBetween(1, 1000),
            ],
            'changed_fields' => fake()->boolean(40) ? ['salary' => ['from' => 1200, 'to' => 1500]] : null,
            'affects_salary' => fake()->boolean(),
            'affects_status' => fake()->boolean(),
            'affects_position_type' => fake()->boolean(),
            'affects_staff_type' => fake()->boolean(),
            'affects_date_start' => fake()->boolean(),
            'affects_date_end' => fake()->boolean(),
            'affects_clinical' => fake()->boolean(),
            'affects_vacation_policy' => fake()->boolean(),
            'affects_place' => fake()->boolean(),
            'affects_act_number' => fake()->boolean(),
        ];
    }
}
