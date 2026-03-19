<?php

namespace App\Filament\Resources\Positions\Tables;

use App\Enums\PositionStatus;
use App\Models\Position;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PositionsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('employee.name')
                    ->label(__('filament/admin/position_resource.employee.name'))
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
                    ->formatStateUsing(fn ($state) => $state?->label(__('filament/admin/position_resource.position_type')))
                    ->label('Position Type')
                    ->sortable(),
                TextColumn::make('date_start')
                    ->label(__('filament/admin/position_resource.date_start'))
                    ->date()
                    ->sortable(),
                TextColumn::make('date_end')
                    ->label(__('filament/admin/position_resource.date_end'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament/admin/position_resource.status'))
                    ->badge()
                    ->formatStateUsing(fn (PositionStatus $state): string => $state->getLabel())
                    ->color(fn (PositionStatus $state): string|array|null => $state->getColor())
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('salary')
                    ->label(__('filament/admin/position_resource.salary'))
                    ->money('GEL')
                    ->sortable(),
                TextColumn::make('act_number')
                    ->label(__('filament/admin/position_resource.act_number'))
                    ->alignCenter()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('act_date')
                    ->label(__('filament/admin/position_resource.act_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('automative_renewal')
                    ->label(__('filament/admin/position_resource.automative_renewal'))
                    ->alignCenter()
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('comment')
                    ->label(__('filament/admin/position_resource.comment'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/position_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament/admin/position_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('filament/admin/position_resource.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
