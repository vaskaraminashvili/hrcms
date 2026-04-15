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
    public static function configure(Table $table, bool $hideEmployeeAndPositionColumns = false): Table
    {
        $columns = [];

        $columns[] = TextColumn::make('type')
            ->label(__('filament.vacation_type'))
            ->badge()
            ->alignCenter()
            ->searchable();
        if (! $hideEmployeeAndPositionColumns) {
            $columns[] = TextColumn::make('employee.name')
                ->label(__('filament.employee.name'))
                ->formatStateUsing(function (string $state, Vacation $record): string {
                    return $record->employee->name.' '.$record->employee->surname;
                })
                ->searchable();
            $columns[] = TextColumn::make('position.id')
                ->label(__('filament.position'))
                ->formatStateUsing(function (string $state, Vacation $record): string {
                    return $record->position->place->name.'/'.$record->position->department->name;
                })
                ->searchable();
        }

        $columns = array_merge($columns, [
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
        ]);

        return $table
            ->columns($columns)
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
            ])
            ->defaultSort('id', 'desc');
    }
}
