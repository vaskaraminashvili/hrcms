<?php

namespace App\Filament\Resources\Employees\Actions;

use App\Models\Employee;
use App\Models\User;
use App\Services\EmployeeUserService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateEmployeeUserAction
{
    public static function make(string $name = 'createUser'): Action
    {
        return Action::make($name)
            ->label('')
            ->icon('heroicon-m-user-plus')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('filament.admin.edit_employee.create_user_modal_heading'))
            ->modalDescription(__('filament.admin.edit_employee.create_user_modal_description'))
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
                ->title(__('filament.admin.edit_employee.create_user_not_allowed'))
                ->send();

            return;
        }

        try {
            app(EmployeeUserService::class)->createForEmployee($employee);
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title(__('filament.admin.edit_employee.create_user_failed'))
                ->body($exception->getMessage())
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title(__('filament.admin.edit_employee.create_user_success'))
            ->send();
    }
}
