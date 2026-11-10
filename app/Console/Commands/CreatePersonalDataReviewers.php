<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\User;
use App\Support\PersonalDataReviewer;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreatePersonalDataReviewers extends Command
{
    protected $signature = 'users:create-personal-data-reviewers';

    protected $description = 'Create one personal data reviewer login for each faculty department';

    private const TEMPORARY_PASSWORD = 'password';

    public function handle(): int
    {
        $role = Role::findOrCreate(PersonalDataReviewer::ROLE, 'web');

        $departments = Department::query()
            ->whereIn('id', PersonalDataReviewer::FACULTY_DEPARTMENT_IDS)
            ->get()
            ->keyBy(fn (Department $department): int => (int) $department->getKey());

        $missing = array_values(array_diff(
            PersonalDataReviewer::FACULTY_DEPARTMENT_IDS,
            $departments->keys()->all(),
        ));

        if ($missing !== []) {
            $this->error('Missing departments: '.implode(', ', $missing));

            return self::FAILURE;
        }

        $rows = [];

        foreach (PersonalDataReviewer::FACULTY_DEPARTMENT_IDS as $departmentId) {
            $department = $departments->get($departmentId);
            $email = 'pdr-'.$department->id.'@tsmu.edu';
            $user = User::query()->where('email', $email)->first();

            if ($user === null) {
                $user = User::query()->create([
                    'name' => $department->name,
                    'email' => $email,
                    'password' => self::TEMPORARY_PASSWORD,
                    'department_id' => $department->id,
                    'email_verified_at' => now(),
                    'force_renew_password' => true,
                ]);
            } else {
                $user->fill([
                    'name' => $department->name,
                    'department_id' => $department->id,
                    'password' => self::TEMPORARY_PASSWORD,
                    'force_renew_password' => true,
                ])->save();
            }

            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }

            $rows[] = [$department->id, $department->name, $email, self::TEMPORARY_PASSWORD];
        }

        $this->table(['Department', 'Name', 'Email', 'Temporary password'], $rows);

        return self::SUCCESS;
    }
}
