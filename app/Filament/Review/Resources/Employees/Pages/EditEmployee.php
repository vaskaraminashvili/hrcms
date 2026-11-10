<?php

namespace App\Filament\Review\Resources\Employees\Pages;

use App\Filament\Resources\Employees\Pages\EditEmployee as AdminEditEmployee;
use App\Filament\Review\Resources\Employees\EmployeeResource;

class EditEmployee extends AdminEditEmployee
{
    protected static string $resource = EmployeeResource::class;
}
