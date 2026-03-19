<?php

namespace Database\Factories;

use App\Models\ComputerSkill;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComputerSkillFactory extends Factory
{
    protected $model = ComputerSkill::class;

    public function definition(): array
    {
        $programs = [
            'Microsoft Word', 'Microsoft Excel', 'Microsoft PowerPoint',
            'Adobe Photoshop', 'Adobe Illustrator', 'AutoCAD',
            'SPSS', 'MATLAB', 'R Studio', 'Python', 'STATA',
        ];

        $levels = [
            ['ka' => 'კარგად',   'en' => 'Good'],
            ['ka' => 'საშუალოდ', 'en' => 'Average'],
        ];

        $program = $this->faker->randomElement($programs);

        return [
            'employee_id' => Employee::factory(),
            'title' => [
                'ka' => $program,
                'en' => $program,
            ],
            'level' => $this->faker->randomElement($levels),
        ];
    }

    public function good(): static
    {
        return $this->state([
            'level' => ['ka' => 'კარგად', 'en' => 'Good'],
        ]);
    }

    public function average(): static
    {
        return $this->state([
            'level' => ['ka' => 'საშუალოდ', 'en' => 'Average'],
        ]);
    }
}
