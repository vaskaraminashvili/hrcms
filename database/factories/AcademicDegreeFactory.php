<?php

namespace Database\Factories;

use App\Models\AcademicDegree;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicDegreeFactory extends Factory
{
    protected $model = AcademicDegree::class;

    public function definition(): array
    {
        $degrees = [
            ['ka' => 'დოქტორი', 'en' => 'Doctor'],
            ['ka' => 'მაგისტრი', 'en' => 'Master'],
            ['ka' => 'სხვა',    'en' => 'Other'],
        ];

        $degree = $this->faker->randomElement($degrees);
        $isOther = $degree['ka'] === 'სხვა';

        return [
            'employee_id' => Employee::factory(),
            'degree'      => $degree,
            'other'       => $isOther
                ? ['ka' => $this->faker->jobTitle(), 'en' => $this->faker->jobTitle()]
                : null,
        ];
    }

    public function doctor(): static
    {
        return $this->state(['degree' => ['ka' => 'დოქტორი', 'en' => 'Doctor'], 'other' => null]);
    }

    public function master(): static
    {
        return $this->state(['degree' => ['ka' => 'მაგისტრი', 'en' => 'Master'], 'other' => null]);
    }

    public function other(string $titleKa, string $titleEn): static
    {
        return $this->state([
            'degree' => ['ka' => 'სხვა', 'en' => 'Other'],
            'other'  => ['ka' => $titleKa, 'en' => $titleEn],
        ]);
    }
}
