<?php

namespace Database\Factories;

use App\Enums\PositionHistoryAffectField;
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
            ],
            'changed_fields' => fake()->boolean(40) ? ['salary' => ['from' => 1200, 'to' => 1500]] : null,
            ...collect(PositionHistoryAffectField::cases())
                ->mapWithKeys(fn (PositionHistoryAffectField $field) => [$field->value => fake()->boolean()])
                ->all(),
        ];
    }
}
