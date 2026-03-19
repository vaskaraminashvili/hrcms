<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\ScientificProject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScientificProjectFactory extends Factory
{
    protected $model = ScientificProject::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-10 years', '-1 year');
        $end   = $this->faker->dateTimeBetween($start, 'now');

        return [
            'employee_id' => Employee::factory(),
            'project_name' => [
                'ka' => $this->faker->bs() . ' პროექტი',
                'en' => $this->faker->bs() . ' Project',
            ],
            'institution' => [
                'ka' => $this->faker->company() . ' ფონდი',
                'en' => $this->faker->company() . ' Foundation',
            ],
            'position' => [
                'ka' => $this->faker->randomElement(['მთავარი მკვლევარი', 'თანამკვლევარი', 'კოორდინატორი']),
                'en' => $this->faker->randomElement(['Principal Investigator', 'Co-Investigator', 'Coordinator']),
            ],
            'started_at' => $start,
            'ended_at'   => $end,
        ];
    }

    public function ongoing(): static
    {
        return $this->state(['ended_at' => null]);
    }
}
