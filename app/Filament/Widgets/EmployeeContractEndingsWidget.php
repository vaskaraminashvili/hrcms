<?php

namespace App\Filament\Widgets;

use App\Enums\EmployeeStatusEnum;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use App\Models\Position;
use Carbon\CarbonInterface;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class EmployeeContractEndingsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->contractEndingsThisMonthQuery())
            ->heading(__('filament.employee_date_end_this_month'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->formatStateUsing(fn (string $state, Employee $record): string => $record->name.' '.$record->surname)
                    ->url(fn (Employee $record): string => EmployeeResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('date_end_this_month')
                    ->label(__('filament.date_end_placeholder'))
                    ->state(fn (Employee $record): ?string => $this->endingPosition($record)?->date_end?->format('d.m.Y')),
                TextColumn::make('date_end_diff')
                    ->label(__('filament.date_end_diff_placeholder'))
                    ->state(fn (Employee $record): ?string => $this->endingPosition($record)?->date_end?->locale(app()->getLocale())->diffForHumans()),
                TextColumn::make('mobile_number')
                    ->label(__('filament.mobile_number_placeholder')),
                TextColumn::make('email')
                    ->label(__('filament.email')),
            ]);
    }

    private function contractEndingsThisMonthQuery(): Builder
    {
        [$monthStart, $monthEnd] = $this->currentMonthRange();

        return Employee::query()
            ->where('status', EmployeeStatusEnum::ACTIVE)
            ->whereHas('positions', fn (Builder $query): Builder => $query
                ->whereNotNull('date_end')
                ->whereBetween('date_end', [$monthStart, $monthEnd]))
            ->with(['positions' => fn ($query) => $query
                ->whereNotNull('date_end')
                ->whereBetween('date_end', [$monthStart, $monthEnd])
                ->orderBy('date_end')])
            ->orderBy(
                Position::query()
                    ->selectRaw('MIN(date_end)')
                    ->whereColumn('employee_id', 'employees.id')
                    ->whereNotNull('date_end')
                    ->whereBetween('date_end', [$monthStart, $monthEnd]),
            );
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function currentMonthRange(): array
    {
        return [now()->startOfMonth(), now()->endOfMonth()];
    }

    private function endingPosition(Employee $record): ?Position
    {
        return $record->positions->first();
    }
}
