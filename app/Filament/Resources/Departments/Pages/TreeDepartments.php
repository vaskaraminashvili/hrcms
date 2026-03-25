<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Enums\DepartmentStatus;
use App\Filament\Resources\Departments\DepartmentResource;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Openplain\FilamentTreeView\Resources\Pages\TreePage;

class TreeDepartments extends TreePage
{
    protected static string $resource = DepartmentResource::class;

    public bool $showArchivedDepartments = false;

    public function getTreeQuery(): Builder|Relation
    {
        $query = parent::getTreeQuery();

        if (! $this->showArchivedDepartments) {
            $query->whereNot('status', DepartmentStatus::ARCHIVED);
        }

        return $query;
    }

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
            Action::make('newDepartment')
                ->label(__('filament.tree_departments.new_department'))
                ->icon('heroicon-o-plus')
                ->url(DepartmentResource::getUrl('create')),
        ];
    }
}
