<?php

namespace App\Filament\Review\Resources\Employees\Pages;

use App\Filament\Review\Resources\Employees\EmployeeResource;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return __('filament.admin.list_employees.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.admin.list_employees.title');
    }
}
