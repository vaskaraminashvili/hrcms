<?php

namespace App\Filament\Resources\VacationTransfers;

use App\Filament\Resources\VacationTransfers\Pages\CreateVacationTransfer;
use App\Filament\Resources\VacationTransfers\Pages\EditVacationTransfer;
use App\Filament\Resources\VacationTransfers\Pages\ListVacationTransfers;
use App\Filament\Resources\VacationTransfers\Schemas\VacationTransferForm;
use App\Filament\Resources\VacationTransfers\Tables\VacationTransfersTable;
use App\Models\VacationTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VacationTransferResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = VacationTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return VacationTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VacationTransfersTable::configure($table);
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
            'index' => ListVacationTransfers::route('/'),
            'create' => CreateVacationTransfer::route('/create'),
            'edit' => EditVacationTransfer::route('/{record}/edit'),
        ];
    }
}
