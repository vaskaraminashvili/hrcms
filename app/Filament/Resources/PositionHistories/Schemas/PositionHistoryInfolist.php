<?php

namespace App\Filament\Resources\PositionHistories\Schemas;

use App\Models\PositionHistory;
use App\Models\VacationPolicy;
use DateTimeInterface;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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
                                        str($field)->replace('_', ' ')->title()->toString() => self::formatDiffSegment($diff),
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
    private static function formatDiffSegment(mixed $diff): string
    {
        if (! is_array($diff)) {
            return self::formatDiffValue($diff);
        }

        if (! array_key_exists('from', $diff) && ! array_key_exists('to', $diff)) {
            return json_encode($diff, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return self::formatDiffValue($diff['from'] ?? null).' → '.self::formatDiffValue($diff['to'] ?? null);
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

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
