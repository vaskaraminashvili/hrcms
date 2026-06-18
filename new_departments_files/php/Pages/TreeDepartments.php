<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Models\Department;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\Page;

class TreeDepartments extends Page
{
    protected static string $resource = DepartmentResource::class;

    protected static string $view = 'filament.resources.departments.pages.tree-departments';

    // ── Page header actions ────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('archive')
                ->label(__('filament.resources.departments.show_archive'))
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->url(static::getResource()::getUrl('list')),

            CreateAction::make()
                ->label(__('filament.resources.departments.new_structure'))
                ->icon('heroicon-o-plus'),
        ];
    }

    // ── View data ─────────────────────────────────────────

    public function getViewData(): array
    {
        $records = Department::query()
            ->orderBy('parent_id')
            ->orderBy('order')
            ->with(['children', 'parent'])
            ->get();

        return [
            'records' => $records,
        ];
    }
}
