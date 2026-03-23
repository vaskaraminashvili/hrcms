<?php

namespace App\Filament\Resources\Positions\Tables;

use App\Enums\DepartmentStatus;
use App\Enums\PositionStatus;
use App\Models\Department;
use App\Models\Position;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PositionsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('employee.name')
                    ->label(__('filament.employee.name'))
                    ->formatStateUsing(function (string $state, Position $record): string {
                        return $record->employee->name.' '.$record->employee->surname;
                    })
                    ->description(function (Position $record): string {
                        return $record->department->name;
                    })
                    ->searchable(['employee.name', 'employee.surname'])

                    ->sortable(),
                TextColumn::make('position_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label(__('filament.position_type')))
                    ->label('Position Type')
                    ->sortable(),
                TextColumn::make('date_start')
                    ->label(__('filament.date_start'))
                    ->date()
                    ->sortable(),
                TextColumn::make('date_end')
                    ->label(__('filament.date_end'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament.status'))
                    ->badge()
                    ->formatStateUsing(fn (PositionStatus $state): string => $state->getLabel())
                    ->color(fn (PositionStatus $state): string|array|null => $state->getColor())
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('salary')
                    ->label(__('filament.salary'))
                    ->money('GEL')
                    ->sortable(),
                TextColumn::make('act_number')
                    ->label(__('filament.act_number'))
                    ->alignCenter()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('act_date')
                    ->label(__('filament.act_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('automative_renewal')
                    ->label(__('filament.automative_renewal'))
                    ->alignCenter()
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('comment')
                    ->label(__('filament.comment'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('department_id')
                    ->options(
                        Department::query()
                            ->whereIn('status', [DepartmentStatus::ACTIVE->value, DepartmentStatus::ARCHIVED->value])
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->attribute('department_id'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->recordUrl(
                fn (Position $record): string => route('filament.admin.resources.positions.edit', ['record' => $record]),
            );
    }
}
