<?php

namespace App\Filament\Resources\Positions\Tables;

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
                    ->formatStateUsing(function (string $state, Position $record): string {
                        return $record->employee->name.' '.$record->employee->surname;
                    })
                    ->description(function (Position $record): string {
                        return $record->department->name;
                    })
                    ->searchable(['employee.name', 'employee.surname'])

                    ->sortable(),

                TextColumn::make('date_start')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_end')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        default => 'gray',
                    })
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('act_number')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('act_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('automative_renewal')
                    ->label('Renewal')
                    ->alignCenter()
                    ->boolean(),
                TextColumn::make('salary')
                    ->label('Salary')
                    ->money('GEL')
                    ->sortable(),
                TextColumn::make('comment')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
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
