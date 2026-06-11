<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Enums\DepartmentStatus;
use App\Filament\Resources\Departments\DepartmentResource;
use App\Models\Department;
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
        $query = Department::query()
            ->orderBy('parent_id')
            ->orderBy('order')
            ->with(['children', 'parent']);

        if (! $this->showArchivedDepartments) {
            $query->whereNot('status', DepartmentStatus::ARCHIVED);
        }

        return $query->get();
    }
}
