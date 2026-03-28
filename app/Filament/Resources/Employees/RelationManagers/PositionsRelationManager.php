<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Enums\PositionStatus;
use App\Filament\Resources\Positions\Schemas\PositionForm;
use App\Models\Position;
use App\Services\PositionFormPersistence;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->where('status', 'like', '%'.$search.'%');
                    })
                    ->sortable(),
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
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('filament.relation_managers.positions.add_new_position'))
                    ->using(function (array $data, RelationManager $livewire): Model {
                        $data = PositionFormPersistence::prepareDataForCreate($data);

                        return PositionFormPersistence::createFromValidatedData($data, $livewire->getOwnerRecord());
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $record = Position::query()->find($data['id']);

                        return array_merge($data, Arr::except(
                            $record?->attributesToArray() ?? [],
                            ['id', 'created_at', 'updated_at'],
                        ));
                    })
                    ->using(function (array $data, RelationManager $livewire, Model $record, ?Table $table): void {
                        PositionFormPersistence::updatePositionAndDetail($record, $data);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
