<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('filament.admin.list_employees.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.admin.list_employees.title');
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->label(__('filament.all'))
                ->badge($this->getModel()::count()),
            'deleted' => Tab::make('Deleted')
                ->label(__('filament.deleted'))
                ->badge($this->getModel()::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->onlyTrashed()),
        ];
    }
}
