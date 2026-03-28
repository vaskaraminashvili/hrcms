<?php

namespace App\Filament\Resources\PositionHistories;

use App\Filament\Resources\PositionHistories\Pages\ListPositionHistories;
use App\Filament\Resources\PositionHistories\Pages\ViewPositionHistory;
use App\Filament\Resources\PositionHistories\Schemas\PositionHistoryInfolist;
use App\Filament\Resources\PositionHistories\Tables\PositionHistoriesTable;
use App\Models\PositionHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PositionHistoryResource extends Resource
{
    protected static ?string $model = PositionHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.position_histories.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.position_histories.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.position_histories.plural_model_label');
    }

    public static function table(Table $table): Table
    {
        return PositionHistoriesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PositionHistoryInfolist::configure($schema);
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
            'index' => ListPositionHistories::route('/'),
            'view' => ViewPositionHistory::route('/{record}'),
        ];
    }
}
