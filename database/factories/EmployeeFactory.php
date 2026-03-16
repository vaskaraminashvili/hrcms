<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'name_eng' => fake()->optional(0.7)->firstName(),
            'surrname_eng' => fake()->optional(0.7)->lastName(),
            'personal_number' => fake()->unique()->numerify('##########'),
            'email' => fake()->optional(0.8)->safeEmail(),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->optional(0.9)->randomElement(['male', 'female']),
            'citizenship' => fake()->optional(0.9)->country(),
            'education' => fake()->optional(0.8)->numberBetween(1, 5),
            'degree' => fake()->optional(0.4)->randomElement(['Bachelor', 'Master', 'PhD', 'Associate']),
            'address' => fake()->optional(0.8)->address(),
            'pysical_address' => fake()->optional(0.6)->address(),
        ];
    }
}
