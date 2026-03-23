<?php

namespace App\Filament\Resources\VacationPolicies\Tables;

use App\Enums\StatusEnum;
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
                    ->icon(function (string $state, VacationPolicy $record): Heroicon {
                        return StatusEnum::from($record->status)->icon();
                    })
                    ->color(function (string $state, VacationPolicy $record): string {
                        return StatusEnum::from($record->status)->color();
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
