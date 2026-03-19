<?php

namespace Database\Factories;

use App\Models\AcademicPosition;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicPositionFactory extends Factory
{
    protected $model = AcademicPosition::class;

    public function definition(): array
    {
        $titles = [
            ['ka' => 'ასისტენტ პროფესორი',  'en' => 'Assistant Professor'],
            ['ka' => 'ასოცირებული პროფესორი', 'en' => 'Associate Professor'],
            ['ka' => 'პროფესორი',             'en' => 'Professor'],
        ];

        return [
            'employee_id' => Employee::factory(),
            'title'       => $this->faker->randomElement($titles),
        ];
    }
}
