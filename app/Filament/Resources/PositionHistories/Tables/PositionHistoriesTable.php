<?php

namespace App\Filament\Resources\PositionHistories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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
                TernaryFilter::make('affects_salary')
                    ->label('Salary'),
                TernaryFilter::make('affects_status')
                    ->label('Status'),
                TernaryFilter::make('affects_position_type')
                    ->label('Position type'),
                TernaryFilter::make('affects_staff_type')
                    ->label('Staff type'),
                TernaryFilter::make('affects_date_start')
                    ->label('Date start'),
                TernaryFilter::make('affects_date_end')
                    ->label('Date end'),
                TernaryFilter::make('affects_clinical')
                    ->label('Clinical'),
                TernaryFilter::make('affects_vacation_policy')
                    ->label('Vacation policy'),
                TernaryFilter::make('affects_place')
                    ->label('Place'),
                TernaryFilter::make('affects_act_number')
                    ->label('Act number'),
            ])
            ->filtersFormColumns(4)
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
