<?php

namespace App\Filament\Resources\PublicHolidays\Tables;

use App\Enums\PublicHolidayKind;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PublicHolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->date('d.m.Y')
                    ->sortable()
                    ->label(__('filament.date')),
                TextColumn::make('kind')
                    ->badge()
                    ->formatStateUsing(fn (PublicHolidayKind $state): string => $state->label())
                    ->sortable()
                    ->label(__('filament.public_holiday_kind.title')),
                TextColumn::make('name')
                    ->searchable()
                    ->placeholder('—')
                    ->label(__('filament.public_holiday_name')),
                TextColumn::make('series_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('filament.public_holiday_series_id')),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
