<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeIds = Employee::query()->pluck('id');
        $departmentIds = Department::query()->pluck('id');

        if ($employeeIds->isEmpty() || $departmentIds->isEmpty()) {
            return;
        }

        $pairs = collect();
        foreach ($employeeIds as $employeeId) {
            foreach ($departmentIds as $departmentId) {
                $pairs->push([$employeeId, $departmentId]);
            }
        }

        $count = min(100, $pairs->count());

        foreach ($pairs->shuffle()->take($count) as [$employeeId, $departmentId]) {
            Position::factory()->create([
                'employee_id' => $employeeId,
                'department_id' => $departmentId,
            ]);
        }
    }
}
