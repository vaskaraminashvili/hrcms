<?php

namespace App\Filament\Resources\PublicHolidays;

use App\Filament\Resources\PublicHolidays\Pages\CreatePublicHoliday;
use App\Filament\Resources\PublicHolidays\Pages\EditPublicHoliday;
use App\Filament\Resources\PublicHolidays\Pages\ListPublicHolidays;
use App\Filament\Resources\PublicHolidays\Schemas\PublicHolidayForm;
use App\Filament\Resources\PublicHolidays\Tables\PublicHolidaysTable;
use App\Models\PublicHoliday;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PublicHolidayResource extends Resource
{
    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $model = PublicHoliday::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'date';

    public static function form(Schema $schema): Schema
    {
        return PublicHolidayForm::configureForEdit($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicHolidaysTable::configure($table);
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
            'index' => ListPublicHolidays::route('/'),
            'create' => CreatePublicHoliday::route('/create'),
            'edit' => EditPublicHoliday::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.public_holidays.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.public_holidays.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.public_holidays.plural_model_label');
    }
}
