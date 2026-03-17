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
        $positionTypes = [
            ['name' => 'ემერიტუსი'],
            ['name' => 'ადმინისტრაციული პერსონალი'],
            ['name' => 'დამხმარე ადმინისტრაციული პერსონალი'],
            ['name' => 'აკადემიური პერსონალი'], // if it has this add fields
            ['name' => 'მოწვეული მასწავლებელი'],
            ['name' => 'ხელშეკრულებით დასაქმებული'], // if this add automative renewable
            ['name' => 'აკადემიური წოდება'],
        ];

        // fields
        // theoretical or Clinical toggle
        // if clinical add CLINIcs text field
        PositionType::insert($positionTypes);
    }
}
