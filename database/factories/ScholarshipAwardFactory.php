<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\ScholarshipAward;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScholarshipAwardFactory extends Factory
{
    protected $model = ScholarshipAward::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'title' => [
                'ka' => $this->faker->bs() . ' სტიპენდია',
                'en' => $this->faker->bs() . ' Scholarship',
            ],
            'issuer' => [
                'ka' => $this->faker->company() . ' ფონდი',
                'en' => $this->faker->company() . ' Foundation',
            ],
            'issued_at' => $this->faker->dateTimeBetween('-10 years', 'now'),
        ];
    }
}
