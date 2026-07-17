<?php

namespace App\Filament\Exports;

use App\Models\Employee;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    /**
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label(__('filament.name')),
            ExportColumn::make('surname')
                ->label(__('filament.surname')),
            ExportColumn::make('personal_number')
                ->label(__('filament.personal_number')),

            // ── Add more columns below ─────────────────────────────────────────
            //
            // Plain attribute:
            //     ExportColumn::make('email'),
            //
            // Relationship column via dot notation (eager loading is automatic):
            //     ExportColumn::make('user.email'),
            //     ExportColumn::make('positions.department.name')
            //         ->listAsJson(), // has-many chains produce multiple values
            //
            // Aggregate columns (counts / averages / sums on relationships):
            //     ExportColumn::make('positions_count')
            //         ->counts('positions'),
            //     ExportColumn::make('positions_avg_salary')
            //         ->avg('positions', 'salary'),
            //     ExportColumn::make('positions_sum_salary')
            //         ->sum('positions', 'salary'),
            //
            // Computed state / formatting:
            //     ExportColumn::make('full_name')
            //         ->state(fn (Employee $record): string => $record->full_name),
            //     ExportColumn::make('birth_date')
            //         ->formatStateUsing(fn ($state): string => $state?->format('d.m.Y') ?? ''),
            // ───────────────────────────────────────────────────────────────────
        ];
    }

    /**
     * Run the export immediately in the request instead of on the queue.
     */
    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
