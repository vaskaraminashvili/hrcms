<?php

namespace App\Filament\Resources\PositionHistories\Tables;

use App\Enums\PositionHistoryAffectField;
use App\Models\Department;
use App\Models\Place;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class Filters
{
    public static function getFilters(): array
    {
        $filters = [];
        $filters[] = SelectFilter::make('department_id')
            ->label(__('filament.department.name'))
            ->options(
                Department::all()
                    ->pluck('name', 'id')
                    ->map(fn (?string $name): string => $name ?? '')
            )
            ->query(function (Builder $query, array $data): Builder {

                return $query->when($data['value'], function (Builder $query, $value): Builder {

                    return $query->whereJsonContains('snapshot', ['department_id' => (int) $value]);
                });
            })
            ->columnSpan(2)
            ->searchable()
            ->preload()
            ->attribute('department_id');
        $filters[] = SelectFilter::make('place_id')
            ->label(__('filament.place'))
            ->options(
                Place::all()
                    ->pluck('name', 'id')
                    ->map(fn (?string $name): string => $name ?? '')
            )
            ->query(function (Builder $query, array $data): Builder {
                return $query->when($data['value'], function (Builder $query, $value): Builder {
                    return $query->whereJsonContains('snapshot', ['place_id' => (int) $value]);
                });
            })
            ->columnSpan(2)
            ->searchable()
            ->preload()
            ->attribute('place_id');
        $filters = array_merge([...$filters], [
            ...collect(PositionHistoryAffectField::cases())
                ->filter(fn (PositionHistoryAffectField $field) => $field->showInFilter())
                ->map(fn (PositionHistoryAffectField $field) => TernaryFilter::make($field->value)
                    ->label($field->getLabel()))
                ->values()
                ->all(),
        ]);
        $filters[] = Filter::make('created_at')
            ->form([
                DatePicker::make('created_from')
                    ->label('Changes from'),
                DatePicker::make('created_until')
                    ->default(now())
                    ->label('Changes until'),
            ])
            ->columnSpan(2)
            ->columns(2)
            ->query(function (Builder $query, array $data): Builder {

                return $query->when($data['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                    ->when($data['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
            })
            ->indicateUsing(function (array $data): ?string {
                $range = '';
                if (! $data['created_from'] && ! $data['created_until']) {
                    $range = __('filament.range_not_selected');
                }
                if ($data['created_from'] && $data['created_until']) {
                    $range = __('filament.range_selected', [
                        'from' => Carbon::parse($data['created_from'])->toFormattedDateString(),
                        'to' => Carbon::parse($data['created_until'])->toFormattedDateString(),
                    ]);
                }

                return $range;
            });

        return $filters;
    }
}
