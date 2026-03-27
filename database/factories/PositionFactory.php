<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Position;
use App\Models\PositionDetail;
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
            'place_id' => Place::query()->inRandomOrder()->value('id'),
            'employee_id' => Employee::query()->inRandomOrder()->value('id'),
            'department_id' => Department::query()->inRandomOrder()->value('id'),
        ];
    }

    public function configure(): static
    {
        return $this->has(PositionDetail::factory(), 'detail');
    }
}
