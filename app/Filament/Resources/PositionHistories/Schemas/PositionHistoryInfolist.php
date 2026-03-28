<?php

namespace App\Filament\Resources\PositionHistories\Schemas;

use App\Enums\PositionHistoryAffectField;
use App\Enums\PositionHistorySnapshotField;
use App\Models\PositionHistory;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
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
                                    ->except(PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY)
                                    ->mapWithKeys(fn ($diff, $field) => [
                                        PositionHistorySnapshotField::labelForSnapshotKey($field) => self::formatDiffSegment($diff, $field),
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
                    ->schema(
                        collect(PositionHistoryAffectField::cases())
                            ->filter(fn (PositionHistoryAffectField $field) => $field->showInInfolist())
                            ->map(fn (PositionHistoryAffectField $field) => IconEntry::make($field->value)
                                ->label($field->getLabel())
                                ->boolean())
                            ->values()
                            ->all()
                    ),

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
                                    ->except(PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY)
                                    ->mapWithKeys(fn ($value, $key) => [
                                        PositionHistorySnapshotField::labelForSnapshotKey($key) => $value === null
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
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        if ($value instanceof UnitEnum) {
            if ($value instanceof HasLabel) {
                return (string) ($value->getLabel() ?? ($value instanceof \BackedEnum ? $value->value : $value->name));
            }

            return $value instanceof \BackedEnum ? (string) $value->value : $value->name;
        }

        if ($key !== null && ($field = PositionHistorySnapshotField::tryFrom($key))) {
            return $field->formatValue($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
