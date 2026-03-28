<?php

namespace App\Filament\Resources\PositionHistories\Tables;

use App\Enums\PositionHistoryAffectField;
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
                ...collect(PositionHistoryAffectField::cases())
                    ->filter(fn (PositionHistoryAffectField $field) => $field->showInTableColumn())
                    ->map(fn (PositionHistoryAffectField $field) => IconColumn::make($field->value)
                        ->label($field->getLabel())
                        ->boolean())
                    ->values()
                    ->all(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(
                collect(PositionHistoryAffectField::cases())
                    ->filter(fn (PositionHistoryAffectField $field) => $field->showInFilter())
                    ->map(fn (PositionHistoryAffectField $field) => TernaryFilter::make($field->value)
                        ->label($field->getLabel()))
                    ->values()
                    ->all()
            )
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
