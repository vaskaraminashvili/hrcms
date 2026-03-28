<?php

namespace App\Filament\Resources\PositionHistories\Schemas;

use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Models\Place;
use App\Models\PositionHistory;
use App\Models\VacationPolicy;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use UnitEnum;

class PositionHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Meta ────────────────────────────────────────────────────
                Section::make('Event')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('event_type')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'created' => 'success',
                                'updated' => 'info',
                                'deleted' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('position.employee.name')
                            ->label('Employee')
                            ->formatStateUsing(fn ($record) => $record->position->employee->name.' '.
                                $record->position->employee->surname
                            ),
                        TextEntry::make('position.department.name')
                            ->label('Department'),
                        TextEntry::make('changedBy.name')
                            ->label('Changed by')
                            ->default('System'),
                        TextEntry::make('created_at')
                            ->label('Changed at')
                            ->dateTime('d M Y, H:i'),
                    ]),

                // ── What changed ────────────────────────────────────────────
                Section::make('Changed fields')
                    ->description('Before and after values for this save')
                    ->schema([
                        KeyValueEntry::make('changed_fields')
                            ->label('')
                            ->keyLabel('Field')
                            ->valueLabel('Change')
                            ->getStateUsing(function (PositionHistory $record): array {
                                $changed = $record->changed_fields;
                                if (empty($changed) || ! is_array($changed)) {
                                    return [];
                                }

                                return collect($changed)
                                    ->mapWithKeys(fn ($diff, $field) => [
                                        __('filament.changed_fields.'.$field) => self::formatDiffSegment($diff, $field),
                                    ])
                                    ->all();
                            })
                            ->visible(fn ($record) => ! empty($record->changed_fields)),

                        TextEntry::make('no_changes')
                            ->label('')
                            ->default('Initial record — no diff available')
                            ->visible(fn ($record) => empty($record->changed_fields)),
                    ]),

                // ── Affected fields (boolean flags) ─────────────────────────
                Section::make('Affects')
                    ->description('Which tracked fields were touched in this save')
                    ->columns(5)
                    ->schema([
                        IconEntry::make('affects_salary')
                            ->label('Salary')
                            ->boolean(),
                        IconEntry::make('affects_status')
                            ->label('Status')
                            ->boolean(),
                        IconEntry::make('affects_position_type')
                            ->label('Position type')
                            ->boolean(),
                        IconEntry::make('affects_staff_type')
                            ->label('Staff type')
                            ->boolean(),
                        IconEntry::make('affects_date_start')
                            ->label('Date start')
                            ->boolean(),
                        IconEntry::make('affects_date_end')
                            ->label('Date end')
                            ->boolean(),
                        IconEntry::make('affects_clinical')
                            ->label('Clinical')
                            ->boolean(),
                        IconEntry::make('affects_vacation_policy')
                            ->label('Vacation policy')
                            ->boolean(),
                        IconEntry::make('affects_place')
                            ->label('Place')
                            ->boolean(),
                        IconEntry::make('affects_act_number')
                            ->label('Act number')
                            ->boolean(),
                    ]),

                // ── Full snapshot ────────────────────────────────────────────
                Section::make('Full snapshot')
                    ->description('Complete state of the position at the time of this change')
                    ->collapsed()   // collapsed by default — it's a lot of data
                    ->schema([
                        KeyValueEntry::make('snapshot')
                            ->label('')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->getStateUsing(function (PositionHistory $record): array {
                                $snapshot = $record->snapshot;
                                if (empty($snapshot) || ! is_array($snapshot)) {
                                    return [];
                                }

                                return collect($snapshot)
                                    ->mapWithKeys(fn ($value, $key) => [
                                        str($key)->replace('_', ' ')->title()->toString() => $value === null
                                            ? '—'
                                            : self::formatDiffValue($value, $key),
                                    ])
                                    ->all();
                            }),
                    ]),

            ]);
    }

    /**
     * @param  array<string, mixed>|mixed  $diff
     */
    private static function formatDiffSegment(mixed $diff, ?string $key = null): string
    {
        if (! is_array($diff)) {
            return self::formatDiffValue($diff, $key);
        }

        if (! array_key_exists('from', $diff) && ! array_key_exists('to', $diff)) {
            return json_encode($diff, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return self::formatDiffValue($diff['from'] ?? null, $key).' → '.self::formatDiffValue($diff['to'] ?? null, $key);
    }

    private static function formatDiffValue(mixed $value, ?string $key = null): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_array($value)) {
            if ($key === 'vacation_policy') {
                return VacationPolicy::find($value['id'])->name;
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        if ($value instanceof UnitEnum) {
            return $value instanceof \BackedEnum ? (string) $value->value : $value->name;
        }

        if ($key === 'date_start' || $key === 'date_end' || $key === 'act_date' || $key === 'created_at' || $key === 'updated_at') {
            return Carbon::parse($value)->format('d-m-Y');
        }

        if ($key === 'position_type') {
            return PositionType::from($value)->getLabel();
        }

        if ($key === 'comment') {
            return strip_tags($value);
        }

        if ($key === 'place_id') {
            return Place::find($value)->name;
        }

        if ($key === 'salary') {
            return Number::currency(intval($value), 'GEL', 'ka', 0); //
        }

        if ($key === 'status') {
            return PositionStatus::from($value)->getLabel(); //
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
