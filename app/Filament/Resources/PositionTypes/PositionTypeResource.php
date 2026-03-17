<?php

namespace App\Filament\Resources\PositionTypes;

use App\Filament\Resources\PositionTypes\Pages\CreatePositionType;
use App\Filament\Resources\PositionTypes\Pages\EditPositionType;
use App\Filament\Resources\PositionTypes\Pages\ListPositionTypes;
use App\Filament\Resources\PositionTypes\Schemas\PositionTypeForm;
use App\Filament\Resources\PositionTypes\Tables\PositionTypesTable;
use App\Models\PositionType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PositionTypeResource extends Resource
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $model = PositionType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PositionTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PositionTypesTable::configure($table);
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
            'index' => ListPositionTypes::route('/'),
            'create' => CreatePositionType::route('/create'),
            'edit' => EditPositionType::route('/{record}/edit'),
        ];
    }
}
