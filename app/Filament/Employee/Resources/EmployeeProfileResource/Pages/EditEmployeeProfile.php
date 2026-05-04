<?php

namespace App\Filament\Employee\Resources\EmployeeProfileResource\Pages;

use App\Filament\Employee\Resources\EmployeeProfileResource;
use App\Filament\Resources\Employees\Pages\EditEmployee;

class EditEmployeeProfile extends EditEmployee
{
    protected static string $resource = EmployeeProfileResource::class;

    public function mount(int|string|null $record = null): void
    {
        $employee = auth()->user()?->employee;

        abort_if($employee === null, 404);

        parent::mount($employee->getKey());
    }
}
