<?php

namespace App\Filament\Resources\PublicHolidays\Pages;

use App\Filament\Resources\PublicHolidays\PublicHolidayResource;
use App\Filament\Resources\PublicHolidays\Schemas\PublicHolidayForm;
use App\Models\PublicHoliday;
use App\Services\VacationWorkingDaysCalculator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreatePublicHoliday extends CreateRecord
{
    protected static string $resource = PublicHolidayResource::class;

    public function form(Schema $schema): Schema
    {
        return PublicHolidayForm::configureForCreate(
            $this->defaultForm($schema),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();

        if ($end->lt($start)) {
            Notification::make()
                ->title(__('filament.public_holiday_invalid_range_title'))
                ->body(__('filament.public_holiday_invalid_range_body'))
                ->danger()
                ->send();

            throw (new Halt)->rollBackDatabaseTransaction();
        }

        $conflicts = PublicHoliday::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->exists();

        if ($conflicts) {
            Notification::make()
                ->title(__('filament.public_holiday_duplicate_days_title'))
                ->body(__('filament.public_holiday_duplicate_days_body'))
                ->danger()
                ->send();

            throw (new Halt)->rollBackDatabaseTransaction();
        }

        $seriesId = (string) Str::uuid();
        $kind = $data['kind'];
        $name = $data['name'] ?? null;

        $first = null;

        PublicHoliday::withoutEvents(function () use ($start, $end, $seriesId, $kind, $name, &$first): void {
            $period = CarbonPeriod::create($start, $end)
                ->filter(function (Carbon $date) {
                    return $date->isWeekday();
                });
            foreach ($period as $date) {
                $first = PublicHoliday::query()->create([
                    'date' => $date->toDateString(),
                    'kind' => $kind,
                    'series_id' => $seriesId,
                    'name' => $name,
                ]);
            }
        });

        app(VacationWorkingDaysCalculator::class)->recalculateVacationsOverlappingDateRange($start, $end);

        return $first instanceof PublicHoliday ? $first : new PublicHoliday;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
