<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\ForeignLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForeignLanguageFactory extends Factory
{
    protected $model = ForeignLanguage::class;

    public function definition(): array
    {
        $languages = [
            ['ka' => 'ინგლისური',  'en' => 'English'],
            ['ka' => 'გერმანული',  'en' => 'German'],
            ['ka' => 'ფრანგული',   'en' => 'French'],
            ['ka' => 'რუსული',     'en' => 'Russian'],
            ['ka' => 'იტალიური',   'en' => 'Italian'],
            ['ka' => 'ესპანური',   'en' => 'Spanish'],
            ['ka' => 'თურქული',    'en' => 'Turkish'],
            ['ka' => 'არაბული',    'en' => 'Arabic'],
        ];

        $levels = [
            ['ka' => 'კარგად',             'en' => 'Good'],
            ['ka' => 'საშუალოდ',           'en' => 'Average'],
            ['ka' => 'როგორც მშობლიური',   'en' => 'Native'],
        ];

        return [
            'employee_id' => Employee::factory(),
            'language'    => $this->faker->randomElement($languages),
            'level'       => $this->faker->randomElement($levels),
        ];
    }

    public function native(): static
    {
        return $this->state([
            'level' => ['ka' => 'როგორც მშობლიური', 'en' => 'Native'],
        ]);
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
