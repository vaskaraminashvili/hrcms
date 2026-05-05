<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(EmployeeRoleSeeder::class);

        $defaultPassword = (string) config('employees.default_password');
        $emailDomain = (string) config('employees.seed_email_domain');
        $hashedPassword = Hash::make($defaultPassword);

        $usedEmails = [];
        foreach (User::query()->pluck('email') as $existing) {
            $key = Str::lower((string) $existing);
            if ($key !== '') {
                $usedEmails[$key] = true;
            }
        }

        Employee::query()
            ->whereNull('user_id')
            ->orderBy('id')
            ->chunkById(100, function ($employees) use ($hashedPassword, $emailDomain, &$usedEmails): void {
                foreach ($employees as $employee) {
                    /** @var Employee $employee */
                    $name = trim("{$employee->name} {$employee->surname}");

                    $base = $this->normalizedEmployeeEmailCandidate($employee, $emailDomain);
                    $email = $this->firstAvailableEmail($base, $usedEmails);

                    $user = null;
                    for ($attempt = 0; $attempt < 50; $attempt++) {
                        try {
                            $user = User::query()->create([
                                'name' => $name !== '' ? $name : ('Employee '.$employee->id),
                                'email' => $email,
                                'password' => $hashedPassword,
                            ]);
                            break;
                        } catch (QueryException $e) {
                            if (! $this->isUniqueEmailConstraintViolation($e)) {
                                throw $e;
                            }
                            $usedEmails[Str::lower($email)] = true;
                            $email = $this->firstAvailableEmail($base, $usedEmails);
                        }
                    }

                    if ($user === null) {
                        throw new \RuntimeException('Could not assign a unique email for employee '.$employee->getKey().'.');
                    }

                    $usedEmails[Str::lower((string) $user->email)] = true;

                    $user->assignRole('employee');

                    $employee->forceFill(['user_id' => $user->id])->save();
                }
            });
    }

    private function normalizedEmployeeEmailCandidate(Employee $employee, string $emailDomain): string
    {
        $provided = trim((string) $employee->email);

        if ($provided !== '' && filter_var($provided, FILTER_VALIDATE_EMAIL)) {
            return Str::lower($provided);
        }

        $nameAscii = Str::lower(Str::ascii(trim((string) $employee->name)));
        $surnameAscii = Str::lower(Str::ascii(trim((string) $employee->surname)));
        $firstLetter = Str::substr($nameAscii, 0, 1);
        $surnameLocal = Str::slug($surnameAscii, '');

        $local = Str::lower((string) preg_replace('/[^a-z0-9]/', '', $firstLetter.$surnameLocal));

        if ($local === '') {
            $local = Str::slug('employee-'.$employee->personal_number, '-');
            if ($local === '') {
                $local = 'employee-'.$employee->getKey();
            }
        }

        return Str::lower($local.'@'.$emailDomain);
    }

    /**
     * @param  array<string, true>  $usedEmails
     */
    private function firstAvailableEmail(string $baseEmail, array &$usedEmails): string
    {
        $baseEmail = Str::lower(trim($baseEmail));
        $parts = explode('@', $baseEmail, 2);
        $local = $parts[0];
        $domain = $parts[1] ?? '';

        if ($local === '' || $domain === '') {
            $candidate = Str::lower('seed-'.Str::uuid().'@'.($domain !== '' ? $domain : 'invalid.local'));
        } else {
            $candidate = $local.'@'.$domain;
        }

        if (! isset($usedEmails[$candidate])) {
            return $candidate;
        }

        if ($local === '' || $domain === '') {
            throw new \RuntimeException('Could not allocate a unique email for invalid base: '.$baseEmail.'.');
        }
        for ($n = 2; $n < 100_000; $n++) {
            $withSuffix = Str::lower($local.$n.'@'.$domain);
            if (! isset($usedEmails[$withSuffix])) {
                return $withSuffix;
            }
        }

        throw new \RuntimeException('Could not find a free email suffix for '.$baseEmail.'.');
    }

    private function isUniqueEmailConstraintViolation(QueryException $exception): bool
    {
        if ($exception->getCode() === '23000') {
            return true;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'duplicate') || str_contains($message, 'unique');
    }
}
