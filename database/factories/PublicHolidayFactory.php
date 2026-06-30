<?php

namespace Database\Factories;

use App\Enums\PublicHolidayKind;
use App\Models\PublicHoliday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublicHoliday>
 */
class PublicHolidayFactory extends Factory
{
    protected $model = PublicHoliday::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->unique()->dateTimeBetween('now', '+1 year'),
            'kind' => PublicHolidayKind::Regular,
            'series_id' => null,
            'name' => fake()->optional()->words(3, true),
        ];
    }
}
