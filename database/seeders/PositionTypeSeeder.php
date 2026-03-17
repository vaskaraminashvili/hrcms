<?php

namespace Database\Seeders;

use App\Models\PositionType;
use Illuminate\Database\Seeder;

class PositionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PositionType::factory(50)->create();
    }
}
