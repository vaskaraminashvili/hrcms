<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use Filament\Actions\Action;
use Openplain\FilamentTreeView\Resources\Pages\TreePage;

class TreeDepartments extends TreePage
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newDepartment')
                ->label('New Department')
                ->icon('heroicon-o-plus')
                ->url(DepartmentResource::getUrl('create')),
        ];
    }
}
