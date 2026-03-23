<?php

namespace App\Filament\Resources\VacationTransfers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VacationTransfersTable
{
    public static function configure(Table $table, bool $hidePositionColumn = false): Table
    {
        $columns = [];

        if (! $hidePositionColumn) {
            $columns[] = TextColumn::make('position.id')
                ->searchable();
        }

        $columns = array_merge($columns, [
            TextColumn::make('from_year'),
            TextColumn::make('to_year'),
            TextColumn::make('days_count')
                ->numeric()
                ->sortable(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ]);

        return $table
            ->columns($columns)
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
