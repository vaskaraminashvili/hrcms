<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Enums\PositionStatus;
use App\Filament\Resources\Positions\Schemas\PositionForm;
use App\Models\Position;
use App\Services\PositionFormPersistence;
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
                    ->sortable(query: function (Builder $query, string $direction): void {
                        $query->join('position_details', 'positions.id', '=', 'position_details.position_id')
                            ->orderBy('position_details.position_type', $direction)
                            ->select('positions.*');
                    }),
                TextColumn::make('place.name')
                    ->limit(50)
                    ->tooltip(fn (string $state): string => $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_start')
                    ->date()
                    ->sortable(query: function (Builder $query, string $direction): void {
                        $query->join('position_details', 'positions.id', '=', 'position_details.position_id')
                            ->orderBy('position_details.date_start', $direction)
                            ->select('positions.*');
                    }),
                TextColumn::make('date_end')
                    ->date()
                    ->sortable(query: function (Builder $query, string $direction): void {
                        $query->join('position_details', 'positions.id', '=', 'position_details.position_id')
                            ->orderBy('position_details.date_end', $direction)
                            ->select('positions.*');
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PositionStatus $state): string => $state->getLabel())
                    ->color(fn (PositionStatus $state): string => $state->getColor())
                    ->alignCenter()
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->whereHas('detail', fn (Builder $detailQuery): Builder => $detailQuery->where('status', 'like', '%'.$search.'%'));
                    })
                    ->sortable(query: function (Builder $query, string $direction): void {
                        $query->join('position_details', 'positions.id', '=', 'position_details.position_id')
                            ->orderBy('position_details.status', $direction)
                            ->select('positions.*');
                    }),
                TextColumn::make('act_number')
                    ->alignCenter()
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->whereHas('detail', fn (Builder $detailQuery): Builder => $detailQuery->where('act_number', 'like', '%'.$search.'%'));
                    })
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
                    ->sortable(query: function (Builder $query, string $direction): void {
                        $query->join('position_details', 'positions.id', '=', 'position_details.position_id')
                            ->orderBy('position_details.salary', $direction)
                            ->select('positions.*');
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('comment')
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->whereHas('detail', fn (Builder $detailQuery): Builder => $detailQuery->where('comment', 'like', '%'.$search.'%'));
                    })
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
                        if ($record?->detail) {
                            return array_merge($data, Arr::except(
                                $record->detail->attributesToArray(),
                                ['id', 'position_id', 'created_at', 'updated_at'],
                            ));
                        }

                        return $data;
                    })
                    ->using(function (array $data, RelationManager $livewire, Model $record, ?Table $table): void {
                        PositionFormPersistence::updatePositionAndDetail($record, $data);
                    }),
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
