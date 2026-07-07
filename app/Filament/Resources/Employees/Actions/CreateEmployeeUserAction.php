<?php

namespace App\Filament\Resources\Employees\Actions;

use App\Models\Employee;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateEmployeeUserAction
{
    public static function make(string $name = 'createUser'): Action
    {
        return Action::make($name)
            ->label('')
            ->icon('heroicon-m-user-plus')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (Employee $record): void {
                self::create($record);
            });
    }

    public static function canCreate(Employee $employee): bool
    {
        $authUser = Auth::user();

        if (! $authUser instanceof User) {
            return false;
        }

        if (! $authUser->hasRole(config('filament-shield.super_admin.name'))) {
            return false;
        }

        return $employee->user_id === null && ! $employee->trashed();
    }

    public static function create(Employee $employee): void
    {
        if (! self::canCreate($employee)) {
            Notification::make()
                ->warning()
                ->title(__('filament::notifications.danger') ?? 'Action not allowed.')
                ->send();

            return;
        }

        $email = self::resolveUniqueEmail($employee);

        try {
            $user = User::query()->create([
                'name' => trim("{$employee->name} {$employee->surname}") !== ''
                    ? trim("{$employee->name} {$employee->surname}")
                    : 'Employee '.$employee->id,
                'email' => $email,
                'password' => Hash::make((string) config('employees.default_password', 'password')),
                'force_renew_password' => true,
            ]);
        } catch (QueryException $exception) {
            Notification::make()
                ->danger()
                ->title(__('filament::notifications.danger') ?? 'Failed to create user.')
                ->body($exception->getMessage())
                ->send();

            return;
        }

        $user->assignRole('employee');

        $employee->forceFill(['user_id' => $user->id])->save();

        Notification::make()
            ->success()
            ->title(__('filament::notifications.success') ?? 'User created.')
            ->send();
    }

    private static function resolveUniqueEmail(Employee $employee): string
    {
        $existingEmail = Str::lower(trim((string) $employee->email));
        if ($existingEmail !== '' && ! User::query()->where('email', $existingEmail)->exists()) {
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

        $base = Str::lower($local.'@'.$domain);

        if (! User::query()->where('email', $base)->exists()) {
            return $base;
        }

        for ($suffix = 2; $suffix < 100_000; $suffix++) {
            $candidate = Str::lower($local.$suffix.'@'.$domain);

            if (! User::query()->where('email', $candidate)->exists()) {
                return $candidate;
            }
        }

        return Str::lower('employee-'.$employee->getKey().'-'.Str::uuid().'@'.$domain);
    }
}

