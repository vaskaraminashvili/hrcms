<?php

namespace Database\Factories;

use App\Enums\EnumsDepartmentColor;
use App\Enums\EnumsDepartmentType;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true).' '.fake()->companySuffix();

        return [
            'name' => mb_convert_case($name, MB_CASE_TITLE),
            'slug' => Str::slug($name.'-'.fake()->unique()->numberBetween(1, 9999)),
            'description' => fake()->optional(0.7)->realText(200),
            'color' => fake()->randomElement(EnumsDepartmentColor::cases()),
            'type' => fake()->randomElement(EnumsDepartmentType::cases()),
            'author_id' => User::factory(),
            'is_active' => fake()->boolean(90),
            'parent_id' => null,
            'order' => fake()->numberBetween(0, 100),
        ];
    }
}
