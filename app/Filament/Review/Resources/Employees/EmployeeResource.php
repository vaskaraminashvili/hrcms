<?php

namespace App\Filament\Review\Resources\Employees;

use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Schemas\EmployeeInfolist;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Filament\Review\Resources\Employees\Pages\EditEmployee;
use App\Filament\Review\Resources\Employees\Pages\ListEmployees;
use App\Models\Employee;
use App\Models\User;
use App\Support\PersonalDataReviewer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $slug = 'employees';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user instanceof User || ! PersonalDataReviewer::isReviewer($user)) {
            return $query->whereIn($query->getModel()->getQualifiedKeyName(), []);
        }

        return PersonalDataReviewer::scopeEmployees($query, $user);
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.employees.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.employees.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.employees.plural_model_label');
    }
}
