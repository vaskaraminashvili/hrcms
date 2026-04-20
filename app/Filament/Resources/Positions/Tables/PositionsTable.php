<?php

namespace App\Filament\Resources\Positions\Tables;

use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Models\Position;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PositionsTable
{
    public static function configure(Table $table): Table
    {
        $filters = Filters::getFilters();

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
                    ->searchable(query: function (Builder $query, string $search): void {
                        $pattern = '%'.$search.'%';

                        $query->whereHas('employee', function (Builder $employeeQuery) use ($pattern): void {
                            $employeeQuery
                                ->where('name', 'like', $pattern)
                                ->orWhere('surname', 'like', $pattern);
                        })
                            ->orWhereHas('department', function (Builder $departmentQuery) use ($pattern): void {
                                $departmentQuery
                                    ->where('name', 'like', $pattern);
                            });
                    })
                    ->wrap()

                    ->sortable(),
                TextColumn::make('place.name')
                    ->limit(30, '...')
                    ->label(__('filament.place_id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('position_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label(__('filament.position_type')))
                    ->label(__('filament.position_type'))
                    ->searchable(query: function (Builder $query, string $search): void {

                        $positionTypes = PositionType::fromLabel($search);
                        if ($positionTypes) {

                            $pattern = '%'.$positionTypes->value.'%';
                            $query->where('position_type', 'like', $pattern);
                        }
                    })

                    ->sortable(),
                TextColumn::make('date_start')
                    ->label(__('filament.position_date_range'))
                    ->sortable()
                    ->getStateUsing(function (Position $record): string {
                        $start_date = $record->date_start ? Carbon::parse($record->date_start)->format('d.m.Y') : '';
                        $end_date = $record->date_end ? Carbon::parse($record->date_end)->format('d.m.Y') : 'N/A';

                        return $start_date.' - '.($end_date ?? 'N/A');
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status')
                    ->label(__('filament.status'))
                    ->badge()
                    ->formatStateUsing(fn (PositionStatus $state): string => $state->getLabel())
                    ->color(fn (PositionStatus $state): string|array|null => $state->getColor())
                    ->alignCenter()

                    ->searchable(),
                TextColumn::make('salary')
                    ->label(__('filament.salary'))
                    ->money('GEL', decimalPlaces: 0)
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
            ])
            ->filters($filters, layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->recordActions([
                EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil'),
                Action::make('position_history')
                    ->label('')
                    ->icon('heroicon-o-clock')
                    ->url(function (Position $record): string {

                        $attributes['filters[department_id][value]'] = $record->department_id;
                        $attributes['filters[place_id][value]'] = $record->place_id;
                        $attributes['filters[created_at][created_until]'] = now()->format('Y-m-d');

                        if ($record->employee->name) {
                            $attributes['search'] = $record->employee->name.' '.$record->employee->surname;
                        }

                        return route('filament.admin.resources.position-histories.index', $attributes);
                    })
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('id', 'desc')
            ->toolbarActions([

            ])->recordUrl(
                fn (Position $record): string => route('filament.admin.resources.positions.edit', ['record' => $record]),
            );
    }
}
