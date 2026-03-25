<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Enums\PositionStatus;
use App\Filament\Resources\Positions\Schemas\PositionForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PositionsRelationManager extends RelationManager
{
    protected static string $relationship = 'positions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.relation_managers.positions.title');
    }

    public function form(Schema $schema): Schema
    {
        return PositionForm::configure($schema, false);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date_start')
            ->columns([
                TextColumn::make('department.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->sortable(),
                TextColumn::make('place.name')
                    ->limit(50)
                    ->tooltip(fn (string $state): string => $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_start')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_end')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PositionStatus $state): string => $state->getLabel())
                    ->color(fn (PositionStatus $state): string => $state->getColor())
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('act_number')
                    ->alignCenter()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('act_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('automative_renewal')
                    ->label(__('filament.relation_managers.positions.renewal'))
                    ->alignCenter()
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('salary')
                    ->label(__('filament.salary'))
                    ->money('GEL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('filament.relation_managers.positions.add_new_position')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
