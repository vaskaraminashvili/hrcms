<?php

namespace Database\Seeders;

use App\Enums\DepartmentType;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // get all departments
        $departments = Department::all();

        foreach ($departments as $department) {
            $department->type = fake()->randomElement(DepartmentType::cases())->value;
            $department->save();
        }
    }
}
