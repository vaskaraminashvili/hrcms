<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicationFactory extends Factory
{
    protected $model = Publication::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'title' => [
                'ka' => $this->faker->sentence(6),
                'en' => $this->faker->sentence(6),
            ],
            'place' => [
                'ka' => $this->faker->city() . ' - სამეცნიერო ჟურნალი',
                'en' => $this->faker->city() . ' - Scientific Journal',
            ],
            'published_at' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'co_authors' => [
                'ka' => $this->faker->name() . ', ' . $this->faker->name(),
                'en' => $this->faker->name() . ', ' . $this->faker->name(),
            ],
            'page_count' => $this->faker->numberBetween(4, 30),
        ];
    }
}
