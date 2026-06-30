<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\WorkExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkExperienceFactory extends Factory
{
    protected $model = WorkExperience::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-15 years', '-2 years');
        $end   = $this->faker->dateTimeBetween($start, 'now');

        return [
            'employee_id' => Employee::factory(),
            'institution' => [
                'ka' => $this->faker->company() . ' შპს',
                'en' => $this->faker->company() . ' LLC',
            ],
            'position' => [
                'ka' => $this->faker->jobTitle(),
                'en' => $this->faker->jobTitle(),
            ],
            'started_at' => $start,
            'ended_at'   => $end,
        ];
    }

    public function current(): static
    {
        return $this->state(['ended_at' => null]);
    }
}
