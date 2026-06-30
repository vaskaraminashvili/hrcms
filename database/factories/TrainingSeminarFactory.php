<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\TrainingSeminar;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingSeminarFactory extends Factory
{
    protected $model = TrainingSeminar::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-8 years', '-1 month');
        $end   = $this->faker->dateTimeBetween($start, 'now');

        return [
            'employee_id' => Employee::factory(),
            'institution' => [
                'ka' => $this->faker->company() . ' ინსტიტუტი',
                'en' => $this->faker->company() . ' Institute',
            ],
            'topic' => [
                'ka' => $this->faker->catchPhrase() . ' - სემინარი',
                'en' => $this->faker->catchPhrase() . ' - Seminar',
            ],
            'started_at' => $start,
            'ended_at'   => $end,
        ];
    }
}
