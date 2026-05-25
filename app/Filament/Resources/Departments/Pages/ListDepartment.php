<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDepartment extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {

        return [
            CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('filament.admin.list_departments.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.admin.list_departments.title');
    }
}
