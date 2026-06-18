<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Enums\DepartmentStatus;
use App\Filament\Resources\Departments\DepartmentResource;
use App\Models\Department;
use App\Services\DepartmentTreeCacheService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class TreeDepartments extends Page
{
    protected static string $resource = DepartmentResource::class;

    protected string $view = 'filament.resources.departments.pages.tree-departments';

    public bool $showArchivedDepartments = false;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleArchivedDepartments')
                ->label(fn (): string => $this->showArchivedDepartments
                    ? __('filament.tree_departments.hide_archived')
                    : __('filament.tree_departments.show_archived'))
                ->icon(fn (): string => $this->showArchivedDepartments
                    ? 'heroicon-o-eye-slash'
                    : 'heroicon-o-archive-box')
                ->color('gray')
                ->action(function (): void {
                    $this->showArchivedDepartments = ! $this->showArchivedDepartments;
                }),

            CreateAction::make()
                ->label(__('filament.resources.departments.new_structure'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getRecords(): Collection
    {
        return once(function (): Collection {
            $records = app(DepartmentTreeCacheService::class)->getRecords();

            if ($this->showArchivedDepartments) {
                return $records;
            }

            return $records->reject(
                fn (Department $department): bool => $department->status === DepartmentStatus::ARCHIVED,
            );
        });
    }
}
