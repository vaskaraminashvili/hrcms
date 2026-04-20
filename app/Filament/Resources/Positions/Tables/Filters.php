<?php

namespace App\Filament\Resources\Positions\Tables;

use App\Enums\DepartmentStatus;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Models\Department;
use App\Models\Place;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class Filters
{
    public static function getFilters(): array
    {
        $filters = [];

        $filters[] =
        SelectFilter::make('department_id')
            ->label(__('filament.department_id'))
            ->options(
                Department::query()
                    ->whereIn('status', [DepartmentStatus::ACTIVE->value])
                    ->orderBy('name')
                    ->pluck('name', 'id'))
            ->searchable()
            ->preload()
            ->columnSpan(2)
            ->attribute('department_id');

        $filters[] =
        SelectFilter::make('archived_department_id')
            ->label(__('filament.archived_department_id'))
            ->options(
                Department::query()
                    ->whereIn('status', [DepartmentStatus::ARCHIVED->value])
                    ->orderBy('name')
                    ->pluck('name', 'id'))
            ->searchable()
            ->columnSpan(2)
            ->preload()
            ->attribute('department_id');
        $filters = array_merge($filters, [
            SelectFilter::make('place_id')
                ->label(__('filament.place_id'))
                ->options(
                    Place::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                ->searchable()
                ->columnSpan(2)
                ->preload()
                ->attribute('place_id'),
            SelectFilter::make('position_type')
                ->label(__('filament.position_type'))
                ->options(PositionType::class)
                ->searchable()
                ->columnSpan(1)
                ->preload()
                ->attribute('position_type'),
            SelectFilter::make('status')
                ->label(__('filament.status'))
                ->options(PositionStatus::class)
                ->searchable()
                ->columnSpan(1)
                ->preload()
                ->attribute('status'),
            Filter::make('date_range')
                ->label(__('filament.date_range'))
                ->schema([
                    DatePicker::make('date_start')
                        ->label(__('filament.date_start')),

                    DatePicker::make('date_end')
                        ->label(__('filament.date_end')),
                ])
                ->columnSpan(2)
                ->columns(2)
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['date_start'],
                            fn (Builder $query, $date) => $query->whereDate('date_start', '>=', $date),
                        )
                        ->when(
                            $data['date_end'],
                            fn (Builder $query, $date) => $query->whereDate('date_end', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if ($data['date_start'] ?? null) {
                        $indicators['date_start'] = __('filament.date_start').': '.Carbon::parse($data['date_start'])->toFormattedDateString();
                    }
                    if ($data['date_end'] ?? null) {
                        $indicators['date_end'] = __('filament.date_end').': '.Carbon::parse($data['date_end'])->toFormattedDateString();
                    }

                    return $indicators;
                }),
        ]);

        return $filters;
    }
}
