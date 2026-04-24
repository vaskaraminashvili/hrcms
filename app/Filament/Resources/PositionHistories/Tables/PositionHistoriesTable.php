<?php

namespace App\Filament\Resources\PositionHistories\Tables;

use App\Enums\PositionHistoryAffectField;
use App\Enums\PositionStatus;
use App\Models\PositionHistory;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PositionHistoriesTable
{
    public static function configure(Table $table): Table
    {

        $filters = Filters::getFilters();

        return $table
            ->columns([
                TextColumn::make('creted')
                    ->label(__('filament.time'))
                    ->getStateUsing(fn (PositionHistory $record) => $record->created_at->format('d.m.Y'))
                    ->date()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('created_at', $direction);
                    }),
                TextColumn::make('employee')
                    ->getStateUsing(function (PositionHistory $record) {
                        $name = $record->position->employee->name.' '.$record->position->employee->surname;

                        return $name;
                    })
                    ->searchable(true, function (Builder $query, string $search): void {
                        $query->whereHas('position.employee', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', '%'.$search.'%')
                                ->orWhere('surname', 'like', '%'.$search.'%');
                        });
                    })
                    ->label(__('filament.employee_id')),

                TextColumn::make('changes')
                    ->getStateUsing(function (PositionHistory $record) {
                        $changes = collect(PositionHistoryAffectField::cases())
                            ->mapWithKeys(fn (PositionHistoryAffectField $field) => [
                                $field->getLabel() => $field->isAffectedByDirty($record->changed_fields),
                            ])
                            ->filter(fn ($value, $key) => $value)
                            ->keys()
                            ->implode(', ');

                        return $changes;
                    })
                    ->wrap()
                    ->lineClamp(4)
                    ->label(__('filament.changes'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('snapshot.date_start')
                    ->getStateUsing(function (PositionHistory $record): string {
                        $value = $record->snapshot['date_start'] ?? null;

                        if ($value === null || $value === '' || $value === '-') {
                            return '---';
                        }
                        try {
                            return Carbon::parse($value)->format('d.m.Y');
                        } catch (\Throwable) {
                            return '-';
                        }
                    })
                    ->label(__('filament.date_start_short'))
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('snapshot.date_end')
                    ->getStateUsing(function (PositionHistory $record): string {
                        $value = $record->snapshot['date_end'] ?? null;

                        if ($value === null || $value === '' || $value === '-') {
                            return '---';
                        }

                        try {
                            return Carbon::parse($value)->format('d.m.Y');
                        } catch (\Throwable) {
                            return '-';
                        }
                    })
                    ->alignCenter()
                    ->label(__('filament.date_end_short'))
                    ->sortable(),
                TextColumn::make('snapshot.status')
                    ->getStateUsing(function (PositionHistory $record): ?PositionStatus {
                        $value = $record->snapshot['status'] ?? null;

                        if ($value === null || $value === '' || $value === '-') {
                            return null;
                        }

                        return PositionStatus::tryFrom((string) $value);
                    })
                    ->formatStateUsing(fn (?PositionStatus $state): string => $state?->getLabel() ?? '---')
                    ->color(fn (?PositionStatus $state): string|array|null => $state?->getColor())
                    ->badge()
                    ->label(__('filament.status')),
                TextColumn::make('position.department.name')
                    ->wrap()
                    ->searchable(true, function (Builder $query, string $search): void {
                        $query->whereHas('position.department', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', '%'.$search.'%');
                        });
                    })
                    ->label(__('filament.department_id')),
                TextColumn::make('position.place.name')
                    ->wrap()
                    ->copyable()
                    ->searchable(true, function (Builder $query, string $search): void {
                        $query->whereHas('position.place', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', '%'.$search.'%');
                        });
                    })
                    ->label(__('filament.place_id')),

                TextColumn::make('changedBy.name')
                    ->numeric()
                    ->sortable()
                    ->label(__('filament.changed_by'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event_type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->groups([
                Group::make('position.employee.id')
                    ->getTitleFromRecordUsing(function (PositionHistory $record): string {
                        return ucfirst($record->position->employee->name).' '.ucfirst($record->position->employee->surname);
                    })
                    ->orderQueryUsing(fn (Builder $query, string $direction) => $query->orderBy('created_at', $direction))
                    ->label(__('filament.employee_id')),
            ])
            ->defaultSort('id', 'desc')
            ->filters($filters, layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->recordActions([
                ViewAction::make()
                    ->label(__('filament.empty')),
                EditAction::make()
                    ->label(__('filament.empty')),
            ]);
    }
}
