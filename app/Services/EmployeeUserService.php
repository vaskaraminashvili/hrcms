<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class EmployeeUserService
{
    public function createForEmployee(Employee $employee): User
    {
        if ($employee->user_id !== null) {
            $existingUser = $employee->user;

            if ($existingUser !== null) {
                return $existingUser;
            }
        }

        $displayName = trim("{$employee->name} {$employee->surname}");
        if ($displayName === '') {
            $displayName = 'Employee '.$employee->getKey();
        }

        $defaultPassword = (string) config('employees.default_password', 'password');
        $baseEmail = $this->normalizedEmployeeEmailCandidate($employee);
        $email = $this->firstAvailableEmail($baseEmail);

        $user = null;

        for ($attempt = 0; $attempt < 50; $attempt++) {
            try {
                $user = User::query()->create([
                    'name' => $displayName,
                    'email' => $email,
                    'password' => $defaultPassword,
                    'force_renew_password' => true,
                ]);

                break;
            } catch (QueryException $exception) {
                if (! $this->isUniqueEmailConstraintViolation($exception)) {
                    throw $exception;
                }

                $email = $this->firstAvailableEmail($baseEmail, $attempt + 2);
            }
        }

        if ($user === null) {
            throw new \RuntimeException('Could not assign a unique email for employee '.$employee->getKey().'.');
        }

        $user->assignRole('employee');

        $employee->forceFill(['user_id' => $user->id])->save();

        return $user;
    }

    private function normalizedEmployeeEmailCandidate(Employee $employee): string
    {
        $existingEmail = Str::lower(trim((string) $employee->email));
        if ($existingEmail !== '') {
            return $existingEmail;
        }

        $domain = trim((string) config('employees.seed_email_domain'));
        if ($domain === '') {
            $domain = 'invalid.local';
        }

        $local = preg_replace('/\s+/', '', trim((string) $employee->personal_number)) ?? '';
        if ($local === '') {
            $local = 'employee-'.$employee->getKey();
        }

        return Str::lower($local.'@'.$domain);
    }

    private function firstAvailableEmail(string $baseEmail, int $startSuffix = 1): string
    {
        $baseEmail = Str::lower(trim($baseEmail));
        $parts = explode('@', $baseEmail, 2);
        $local = $parts[0];
        $domain = $parts[1] ?? '';

        if ($local === '' || $domain === '') {
            $candidate = Str::lower('employee-'.Str::uuid().'@'.($domain !== '' ? $domain : 'invalid.local'));

            if (! User::query()->where('email', $candidate)->exists()) {
                return $candidate;
            }

            throw new \RuntimeException('Could not allocate a unique email for invalid base: '.$baseEmail.'.');
        }

        if ($startSuffix <= 1) {
            $candidate = Str::lower($local.'@'.$domain);

            if (! User::query()->where('email', $candidate)->exists()) {
                return $candidate;
            }

            $startSuffix = 2;
        }

        for ($suffix = $startSuffix; $suffix < 100_000; $suffix++) {
            $candidate = Str::lower($local.$suffix.'@'.$domain);

            if (! User::query()->where('email', $candidate)->exists()) {
                return $candidate;
            }
        }

        return Str::lower($local.'-'.Str::uuid().'@'.$domain);
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
