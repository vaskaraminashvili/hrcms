<?php

namespace App\Filament\Exports;

use App\Enums\PositionHistorySnapshotField;
use App\Models\Employee;
use App\Models\Position;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
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
            ExportColumn::make('birth_date')
                ->label(__('filament.birth_date'))
                ->formatStateUsing(fn (mixed $state): string => self::formatDate($state)),
            ExportColumn::make('age')
                ->label(__('filament.turning_age'))
                ->state(fn (Employee $record): ?int => $record->birth_date?->age),
            ExportColumn::make('gender')
                ->label(__('filament.gender'))
                ->formatStateUsing(fn (mixed $state): string => $state?->getLabel() ?? ''),
            ExportColumn::make('mobile_number')
                ->label(__('filament.mobile_number')),
            ExportColumn::make('email')
                ->label(__('filament.email')),
            ExportColumn::make('salary')
                ->label(__('filament.salary'))
                ->state(fn (Employee $record): ?int => self::primaryAppointment($record)?->salary),
            ExportColumn::make('position_type')
                ->label(__('filament.position_type'))
                ->state(fn (Employee $record): string => self::primaryAppointment($record)?->position_type?->getLabel() ?? ''),
            ExportColumn::make('position_status')
                ->label(__('filament.status'))
                ->state(fn (Employee $record): string => self::primaryAppointment($record)?->status?->getLabel() ?? ''),
            ExportColumn::make('department')
                ->label(__('filament.department_id'))
                ->state(fn (Employee $record): string => self::departmentLabel(self::primaryAppointment($record))),
            ExportColumn::make('position')
                ->label(__('filament.place_id'))
                ->state(fn (Employee $record): string => self::primaryAppointment($record)?->place?->name ?? ''),
            ExportColumn::make('second_position')
                ->label(__('filament.second_position'))
                ->state(fn (Employee $record): string => self::secondAppointment($record)?->place?->name ?? ''),
            ExportColumn::make('employment_start_date')
                ->label(__('filament.employment_start_date'))
                ->state(fn (Employee $record): string => self::formatDate(self::employmentStartDate($record))),
            ExportColumn::make('work_tenure')
                ->label(__('filament.work_tenure'))
                ->state(fn (Employee $record): string => self::workTenure($record)),
            ExportColumn::make('position_changes')
                ->label(__('filament.position_changes'))
                ->state(fn (Employee $record): string => self::formatPositionChanges($record)),
        ];
    }

    /**
     * @param  Builder<Employee>  $query
     * @return Builder<Employee>
     */
    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with([
            'appointmentPositions.department.parent',
            'appointmentPositions.place',
            'positions.histories',
        ]);
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

    private static function primaryAppointment(Employee $record): ?Position
    {
        return $record->appointmentPositions->first();
    }

    private static function secondAppointment(Employee $record): ?Position
    {
        return $record->appointmentPositions->skip(1)->first();
    }

    private static function departmentLabel(?Position $position): string
    {
        $department = $position?->department;

        if ($department === null) {
            return '';
        }

        if ($department->show_parent == 1) {
            $parentName = $department->parent?->name;
            $departmentName = $department->name;

            return trim(($parentName ? $parentName.' → ' : '').($departmentName ?? ''));
        }

        return $department->name ?? '';
    }

    private static function employmentStartDate(Employee $record): ?CarbonInterface
    {
        $start = $record->positions
            ->pluck('date_start')
            ->filter()
            ->sort()
            ->first();

        return $start instanceof CarbonInterface ? $start : null;
    }

    private static function workTenure(Employee $record): string
    {
        $start = self::employmentStartDate($record);

        if ($start === null) {
            return '';
        }

        return $start->diff(now())->format('%y წელი %m თვე');
    }

    private static function formatPositionChanges(Employee $record): string
    {
        return $record->positions
            ->flatMap(fn (Position $position) => $position->histories)
            ->sortBy('created_at')
            ->map(function ($history): ?string {
                $parts = collect($history->changed_fields ?? [])
                    ->except(PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY)
                    ->map(fn (mixed $diff, string $key): string => PositionHistorySnapshotField::labelForSnapshotKey($key).': '.PositionHistorySnapshotField::formatDiffSegment($diff, $key))
                    ->filter()
                    ->values();

                if ($parts->isEmpty()) {
                    return null;
                }

                $date = $history->created_at?->format('d.m.Y') ?? '';

                return trim($date.' — '.$parts->implode('; '));
            })
            ->filter()
            ->implode("\n");
    }

    private static function formatDate(mixed $state): string
    {
        if ($state instanceof CarbonInterface) {
            return $state->format('d.m.Y');
        }

        return $state ? (string) $state : '';
    }
}
