<?php

namespace App\Filament\Resources\Employees\Actions;

use App\Models\Employee;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ResetEmployeeUserPasswordAction
{
    public static function make(string $name = 'resetUserPassword'): Action
    {
        return Action::make($name)
            ->label('')
            ->icon('heroicon-m-key')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('filament.admin.edit_employee.reset_password_modal_heading'))
            ->modalDescription(__('filament.admin.edit_employee.reset_password_modal_description'))
            ->action(function (Employee $record): void {
                self::reset($record);
            });
    }

    public static function canReset(Employee $employee): bool
    {
        $authUser = Auth::user();

        if (! $authUser instanceof User) {
            return false;
        }

        if (! $authUser->hasRole(config('filament-shield.super_admin.name'))) {
            return false;
        }

        return $employee->user_id !== null && ! $employee->trashed();
    }

    public static function reset(Employee $employee): void
    {
        $user = $employee->user;

        if ($user === null) {
            Notification::make()
                ->warning()
                ->title(__('filament.admin.edit_employee.reset_password_no_user'))
                ->send();

            return;
        }

        $user->update([
            'password' => (string) config('employees.default_password'),
            'force_renew_password' => true,
        ]);

        Notification::make()
            ->success()
            ->title(__('filament.admin.edit_employee.reset_password_success'))
            ->send();
    }
}
