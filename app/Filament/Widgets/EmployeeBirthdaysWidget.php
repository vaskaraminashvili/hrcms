<?php

namespace App\Filament\Widgets;

use App\Enums\EmployeeStatusEnum;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class EmployeeBirthdaysWidget extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->birthdaysThisWeekQuery())
            ->heading(__('filament.employee_birthdays_this_week'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->formatStateUsing(fn (string $state, Employee $record): string => $record->name.' '.$record->surname)
                    ->url(fn (Employee $record): string => EmployeeResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('birthday_this_week')
                    ->label(__('filament.birth_date_placeholder'))
                    ->state(fn (Employee $record): string => $record->birth_date
                        ->format('d.m.Y')),
                TextColumn::make('turning_age')
                    ->label(__('filament.turning_age'))
                    ->state(fn (Employee $record): int => now()->year - $record->birth_date->year),
                TextColumn::make('mobile_number')
                    ->label(__('filament.mobile_number_placeholder')),
                TextColumn::make('email')
                    ->label(__('filament.email')),

            ]);
    }

    private function birthdaysThisWeekQuery(): Builder
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $todayMonthDay = now()->format('m-d');
        $endMonthDay = $weekEnd->format('m-d');

        $query = Employee::query()
            ->where('status', EmployeeStatusEnum::ACTIVE)
            ->whereNotNull('birth_date')
            ->whereNotIn('birth_date', ['1900-01-01', '1800-01-01']);

        if ($weekStart->year === $weekEnd->year) {
            return $query
                ->whereRaw("DATE_FORMAT(birth_date, '%m-%d') BETWEEN ? AND ?", [$todayMonthDay, $endMonthDay])
                ->orderByRaw("DATE_FORMAT(birth_date, '%m-%d') ASC");
        }

        if (now()->month === $weekStart->month) {
            return $query
                ->where(function (Builder $query) use ($todayMonthDay, $endMonthDay): void {
                    $query
                        ->whereRaw("DATE_FORMAT(birth_date, '%m-%d') >= ?", [$todayMonthDay])
                        ->orWhereRaw("DATE_FORMAT(birth_date, '%m-%d') <= ?", [$endMonthDay]);
                })
                ->orderByRaw(
                    "CASE WHEN DATE_FORMAT(birth_date, '%m-%d') >= ? THEN 0 ELSE 1 END ASC, DATE_FORMAT(birth_date, '%m-%d') ASC",
                    [$todayMonthDay],
                );
        }

        return $query
            ->whereRaw("DATE_FORMAT(birth_date, '%m-%d') BETWEEN ? AND ?", [$todayMonthDay, $endMonthDay])
            ->orderByRaw("DATE_FORMAT(birth_date, '%m-%d') ASC");
    }
}
