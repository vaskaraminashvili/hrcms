<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\PositionType;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positionTypes = PositionType::all();

        Position::factory(100)->create()->each(function (Position $position) use ($positionTypes): void {
            $position->positionTypes()->attach(
                $positionTypes->random(fake()->numberBetween(1, 3))->pluck('id')
            );
        });
    }
}
