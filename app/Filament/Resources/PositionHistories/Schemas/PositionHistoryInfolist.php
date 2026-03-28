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

class PositionHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Meta ────────────────────────────────────────────────────
                Section::make(__('filament.position_history_section_event'))
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
                            ->label(__('filament.position_history_employee'))
                            ->formatStateUsing(fn ($record) => $record->position->employee->name.' '.
                                $record->position->employee->surname
                            ),
                        TextEntry::make('position.department.name')
                            ->label(__('filament.position_history_department')),
                        TextEntry::make('changedBy.name')
                            ->label(__('filament.position_history_changed_by'))
                            ->default(__('filament.position_history_changed_by_system')),
                        TextEntry::make('created_at')
                            ->label(__('filament.position_history_changed_at'))
                            ->dateTime('d M Y, H:i'),
                    ]),
                // ── Affected fields (boolean flags) ─────────────────────────
                Section::make(__('filament.position_history_section_affects'))
                    ->description(__('filament.position_history_affects_description'))
                    ->columns(5)
                    ->schema(
                        collect(PositionHistoryAffectField::cases())
                            ->filter(fn (PositionHistoryAffectField $field) => $field->showInInfolist())
                            ->map(fn (PositionHistoryAffectField $field) => IconEntry::make($field->value)
                                ->alignCenter()
                                ->label($field->getLabel())
                                ->boolean())
                            ->values()
                            ->all()
                    ),
                // ── What changed ────────────────────────────────────────────
                Section::make(__('filament.position_history_section_changed_fields'))
                    ->description(__('filament.position_history_changed_fields_description'))
                    ->schema([
                        KeyValueEntry::make('changed_fields')
                            ->label('')
                            ->keyLabel(__('filament.position_history_kv_field'))
                            ->valueLabel(__('filament.position_history_kv_change'))
                            ->getStateUsing(function (PositionHistory $record): array {
                                $changed = $record->changed_fields;
                                if (empty($changed) || ! is_array($changed)) {
                                    return [];
                                }

                                return collect($changed)
                                    ->except(PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY)
                                    ->mapWithKeys(fn ($diff, $field) => [
                                        PositionHistorySnapshotField::labelForSnapshotKey($field) => PositionHistorySnapshotField::formatDiffSegment($diff, $field),
                                    ])
                                    ->all();
                            })
                            ->visible(fn ($record) => ! empty($record->changed_fields)),

                        TextEntry::make('no_changes')
                            ->label('')
                            ->default(__('filament.position_history_initial_no_diff'))
                            ->visible(fn ($record) => empty($record->changed_fields)),
                    ]),

                // ── Full snapshot ────────────────────────────────────────────
                Section::make(__('filament.position_history_section_full_snapshot'))
                    ->description(__('filament.position_history_full_snapshot_description'))
                    ->collapsed()   // collapsed by default — it's a lot of data
                    ->schema([
                        KeyValueEntry::make('snapshot')
                            ->label('')
                            ->keyLabel(__('filament.position_history_kv_field'))
                            ->valueLabel(__('filament.position_history_kv_value'))
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
                                            : PositionHistorySnapshotField::formatDiffValue($value, $key),
                                    ])
                                    ->all();
                            }),
                    ]),

            ]);
    }
}
