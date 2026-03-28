<?php

namespace App\Filament\Resources\PositionHistories\Tables;

use App\Enums\PositionHistoryAffectField;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class Filters
{
    public static function getFilters(): array
    {
        $filters = [
            ...collect(PositionHistoryAffectField::cases())
                ->filter(fn (PositionHistoryAffectField $field) => $field->showInFilter())
                ->map(fn (PositionHistoryAffectField $field) => TernaryFilter::make($field->value)
                    ->label($field->getLabel()))
                ->values()
                ->all(),
        ];
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
