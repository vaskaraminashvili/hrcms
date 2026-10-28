<?php

namespace Database\Factories;

use App\Enums\PublicationScope;
use App\Enums\ScientificForumRole;
use App\Models\Employee;
use App\Models\ScientificForum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScientificForumFactory extends Factory
{
    protected $model = ScientificForum::class;

    public function definition(): array
    {
        $forms = [
            ['ka' => 'ზეპირი მოხსენება',   'en' => 'Oral Presentation'],
            ['ka' => 'პოსტერი',             'en' => 'Poster'],
            ['ka' => 'მოწვეული მომხსენებელი', 'en' => 'Invited Speaker'],
            ['ka' => 'სექციის თავმჯდომარე', 'en' => 'Session Chair'],
        ];

        return [
            'employee_id' => Employee::factory(),
            'title' => [
                'ka' => $this->faker->sentence(5).' - კონფერენცია',
                'en' => $this->faker->sentence(5).' - Conference',
            ],
            'international_forum_publication' => false,
            'held_at' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'participation_form' => $this->faker->randomElement($forms),
            'participation_role' => $this->faker->randomElement([
                ScientificForumRole::Attendee->value,
                ScientificForumRole::Speaker->value,
            ]),
            'scope' => PublicationScope::Local->value,
        ];
    }
}
