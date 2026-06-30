<?php

namespace App\Filament\Resources\VacationPolicies\Tables;

use App\Models\VacationPolicy;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VacationPoliciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->badge()
                    ->icon(function (VacationPolicy $record): Heroicon {
                        return $record->status->getIcon();
                    })
                    ->color(function (VacationPolicy $record): string {
                        return $record->status->getColor();
                    })
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
