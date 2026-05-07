<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class EmployeeRoleSeeder extends Seeder
{
    public function run(): void
    {
        $guardName = config('auth.defaults.guard', 'web');

        Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => $guardName,
        ]);
    }
}
