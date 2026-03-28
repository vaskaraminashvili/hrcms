<?php

namespace App\Filament\Resources\PositionHistories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PositionHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position.id')
                    ->searchable(),
                TextColumn::make('changed_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('event_type')
                    ->searchable(),
                IconColumn::make('affects_salary')
                    ->boolean(),
                IconColumn::make('affects_status')
                    ->boolean(),
                IconColumn::make('affects_position_type')
                    ->boolean(),
                IconColumn::make('affects_staff_type')
                    ->boolean(),
                IconColumn::make('affects_date_start')
                    ->boolean(),
                IconColumn::make('affects_date_end')
                    ->boolean(),
                IconColumn::make('affects_clinical')
                    ->boolean(),
                IconColumn::make('affects_vacation_policy')
                    ->boolean(),
                IconColumn::make('affects_place')
                    ->boolean(),
                IconColumn::make('affects_act_number')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
