<?php

namespace Database\Factories;

use App\Models\Education;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationFactory extends Factory
{
    protected $model = Education::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-20 years', '-5 years');
        $end   = $this->faker->dateTimeBetween($start, 'now');

        return [
            'employee_id' => Employee::factory(),
            'institution' => [
                'ka' => $this->faker->company() . ' უნივერსიტეტი',
                'en' => $this->faker->company() . ' University',
            ],
            'program' => [
                'ka' => $this->faker->bs() . ' პროგრამა',
                'en' => $this->faker->bs() . ' Program',
            ],
            'specialty' => [
                'ka' => $this->faker->jobTitle(),
                'en' => $this->faker->jobTitle(),
            ],
            'started_at' => $start,
            'ended_at'   => $end,
        ];
    }
}
