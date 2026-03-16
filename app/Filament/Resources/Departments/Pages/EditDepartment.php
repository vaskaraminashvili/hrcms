<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Models\Department;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    /**
     * @return array<int|string, string>
     */
    public function getBreadcrumbs(): array
    {
        $record = $this->getRecord();

        if (! $record instanceof Department) {
            return parent::getBreadcrumbs();
        }

        $resource = static::getResource();
        $breadcrumbs = [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
        ];

        foreach ($record->ancestors()->get() as $ancestor) {
            $breadcrumbs[$resource::getUrl('edit', ['record' => $ancestor])] = $ancestor->name;
        }

        $breadcrumbs[] = $record->name;

        return $breadcrumbs;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
