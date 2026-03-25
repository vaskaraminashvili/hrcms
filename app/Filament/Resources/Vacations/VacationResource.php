<?php

namespace App\Filament\Resources\Vacations;

use App\Filament\Resources\Vacations\Pages\CreateVacation;
use App\Filament\Resources\Vacations\Pages\EditVacation;
use App\Filament\Resources\Vacations\Pages\ListVacations;
use App\Filament\Resources\Vacations\Schemas\VacationForm;
use App\Filament\Resources\Vacations\Tables\VacationsTable;
use App\Models\Vacation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VacationResource extends Resource
{
    protected static ?string $model = Vacation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return VacationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VacationsTable::configure($table, false);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVacations::route('/'),
            'create' => CreateVacation::route('/create'),
            'edit' => EditVacation::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.vacations.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.vacations.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.vacations.plural_model_label');
    }
}
