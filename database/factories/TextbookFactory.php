<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Textbook;
use Illuminate\Database\Eloquent\Factories\Factory;

class TextbookFactory extends Factory
{
    protected $model = Textbook::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'title' => [
                'ka' => $this->faker->sentence(4),
                'en' => $this->faker->sentence(4),
            ],
            'publisher' => [
                'ka' => $this->faker->company() . ' გამომცემლობა',
                'en' => $this->faker->company() . ' Publishing',
            ],
            'published_at' => $this->faker->dateTimeBetween('-15 years', 'now'),
            'co_authors' => [
                'ka' => $this->faker->name() . ', ' . $this->faker->name(),
                'en' => $this->faker->name() . ', ' . $this->faker->name(),
            ],
            'page_count' => $this->faker->numberBetween(50, 500),
        ];
    }
}
