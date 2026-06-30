<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleName = config('filament-shield.super_admin.name', 'super_admin');
        $guardName = config('auth.defaults.guard', 'web');

        Role::firstOrCreate(
            ['name' => $roleName, 'guard_name' => $guardName]
        );

        $user = User::query()->orderBy('id')->first();

        if ($user && ! $user->hasRole($roleName)) {
            $user->assignRole($roleName);
        }
    }
}
