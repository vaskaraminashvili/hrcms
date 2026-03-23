<?php

namespace Database\Seeders;

use App\Enums\PositionType;
use App\Enums\StatusEnum;
use App\Models\VacationPolicy;
use Illuminate\Database\Seeder;

class VacationPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PositionType::cases() as $positionType) {
            VacationPolicy::create([
                'position_type' => $positionType->value,
                'name' => $positionType->label(),
                'description' => fake()->realText(200),
                'status' => fake()->randomElement(StatusEnum::cases())->value,
                'settings' => [['key' => 'days', 'value' => '24']],
            ]);
        }
    }
}
