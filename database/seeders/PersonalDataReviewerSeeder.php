<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use App\Support\PersonalDataReviewer;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Models\Role;

class PersonalDataReviewerSeeder extends Seeder
{
    private const TEMPORARY_PASSWORD = 'password';

    /**
     * @var list<array{department_id: int, name: string, email: string}>
     */
    private const REVIEWERS = [
        ['department_id' => 272, 'name' => 'ქეთევან ებრალიძე', 'email' => 'kebralidze@tsmu.edu'],
        ['department_id' => 272, 'name' => 'ნინო გრიგოლია', 'email' => 'ngrigolia@tsmu.edu'],
        ['department_id' => 272, 'name' => 'ირმა გვრიტიშვილი', 'email' => 'igvritishvili@tsmu.edu'],
        ['department_id' => 78, 'name' => 'ინგა კახნიაშვილი', 'email' => 'ikakhniashvili@tsmu.edu'],
        ['department_id' => 78, 'name' => 'სალომე საბანაძე', 'email' => 'ssabanadze@tsmu.edu'],
        ['department_id' => 78, 'name' => 'თინათინ მამაცაშვილი', 'email' => 'tmamatsashvili@tsmu.edu'],
        ['department_id' => 78, 'name' => 'ნათია ჩაფიძე', 'email' => 'nchaphidze@tsmu.edu'],
        ['department_id' => 73, 'name' => 'მანანა სადაღაშვილი', 'email' => 'msadaghashvili@tsmu.edu'],
        ['department_id' => 73, 'name' => 'რუსუდან მამაგულაშვილი', 'email' => 'rmamagulashvili@tsmu.edu'],
        ['department_id' => 75, 'name' => 'ნინო ჯაფარიძე', 'email' => 'njaparidze@tsmu.edu'],
        ['department_id' => 74, 'name' => 'მარიამ ტატანაშვილი', 'email' => 'mtatanashvili@tsmu.edu'],
        ['department_id' => 76, 'name' => 'მაგდა ბიბილაშვილი', 'email' => 'mbibilashvili@tsmu.edu'],
        ['department_id' => 77, 'name' => 'ნინო თეზელაშვილი', 'email' => 'ntezelashvili@tsmu.edu'],
    ];

    public function run(): void
    {
        $role = Role::findOrCreate(PersonalDataReviewer::ROLE, 'web');

        $departmentIds = array_values(array_unique(array_column(self::REVIEWERS, 'department_id')));

        $found = Department::query()
            ->whereIn('id', $departmentIds)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        $missing = array_values(array_diff($departmentIds, $found));

        if ($missing !== []) {
            throw new RuntimeException('Missing departments: '.implode(', ', $missing));
        }

        foreach (self::REVIEWERS as $reviewer) {
            $user = User::query()->where('email', $reviewer['email'])->first();

            if ($user === null) {
                $user = User::query()->create([
                    'name' => $reviewer['name'],
                    'email' => $reviewer['email'],
                    'password' => self::TEMPORARY_PASSWORD,
                    'department_id' => $reviewer['department_id'],
                    'email_verified_at' => now(),
                    'force_renew_password' => true,
                ]);
            } else {
                $user->fill([
                    'name' => $reviewer['name'],
                    'department_id' => $reviewer['department_id'],
                ])->save();
            }

            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        }
    }
}
