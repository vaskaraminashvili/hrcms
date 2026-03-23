<?php

namespace App\Filament\Resources\Vacations\Tables;

use App\Models\Vacation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VacationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label(__('filament.employee.name'))
                    ->formatStateUsing(function (string $state, Vacation $record): string {
                        return $record->employee->name.' '.$record->employee->surname;
                    })
                    ->searchable(),
                TextColumn::make('position.id')
                    ->label(__('filament.position'))
                    ->formatStateUsing(function (string $state, Vacation $record): string {
                        return $record->position->place->name.'/'.$record->position->department->name;
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('start_date')
                    ->label(__('filament.start_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('filament.end_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('working_days_count')
                    ->label(__('filament.working_days_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('filament.type'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('filament.status'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('filament.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('filament.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
