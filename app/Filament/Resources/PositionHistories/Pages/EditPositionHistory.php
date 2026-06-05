<?php

namespace App\Filament\Resources\PositionHistories\Pages;

use App\Enums\PositionHistoryAffectField;
use App\Enums\PositionHistorySnapshotField;
use App\Filament\Resources\PositionHistories\PositionHistoryResource;
use App\Filament\Resources\Positions\Schemas\PositionForm;
use App\Models\Position;
use App\Models\PositionHistory;
use App\Services\PositionFormPersistence;
use Carbon\CarbonInterface;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditPositionHistory extends EditRecord
{
    protected static string $resource = PositionHistoryResource::class;

    public function getTitle(): string
    {
        return __('filament.position_history_edit_title');
    }

    public function form(Schema $schema): Schema
    {
        return PositionForm::configure(
            $this->defaultForm($schema)->model($this->getRecord()->position),
            withEmployee: true,
            employee: null,
            historySnapshot: $this->getRecord(),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var PositionHistory $history */
        $history = $this->getRecord();
        $position = $history->position;
        $snapshot = $history->snapshot ?? [];
        if (! is_array($snapshot)) {
            $snapshot = [];
        }

        $base = $position->exists
            ? Arr::except($position->attributesToArray(), ['created_at', 'updated_at'])
            : [];

        return array_merge($base, $snapshot);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var PositionHistory $history */
        $history = $record;
        $position = $history->position;
        $previousChangedFields = $history->changed_fields;
        if (! is_array($previousChangedFields)) {
            $previousChangedFields = [];
        }

        $fillablePayload = PositionFormPersistence::extractFillableData($data);
        $before = [];
        foreach (array_keys($fillablePayload) as $key) {
            $before[$key] = $position->getAttribute($key);
        }

        Position::withoutEvents(
            fn () => PositionFormPersistence::updatePositionAndDetail($position, $data),
        );

        $position->refresh();

        $diff = [];
        foreach ($before as $key => $from) {
            $to = $position->getAttribute($key);
            if ($this->positionAttributeValuesDiffer($from, $to)) {
                $diff[$key] = [
                    'from' => $from,
                    'to' => $to,
                ];
            }
        }

        $relevantDiff = Arr::except($diff, PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY);
        $mergedChangedFields = $this->mergeChangedFieldsWithPrevious(
            $previousChangedFields,
            $relevantDiff,
        );

        $history->update([
            'changed_by' => auth()->id(),
            'snapshot' => Arr::except($position->toArray(), PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY),
            'changed_fields' => $mergedChangedFields === [] ? null : $mergedChangedFields,
            ...collect(PositionHistoryAffectField::cases())
                ->mapWithKeys(fn (PositionHistoryAffectField $field) => [
                    $field->value => $field->isAffectedByDirty($mergedChangedFields),
                ])
                ->all(),
        ]);

        return $history->refresh();
    }

    /**
     * @param  array<string, mixed>  $previous
     * @param  array<string, array{from: mixed, to: mixed}>  $relevantDiff
     * @return array<string, array{from: mixed, to: mixed}>
     */
    private function mergeChangedFieldsWithPrevious(array $previous, array $relevantDiff): array
    {
        $keys = array_values(array_unique([...array_keys($previous), ...array_keys($relevantDiff)]));
        $merged = [];

        foreach ($keys as $key) {
            $inPrevious = array_key_exists($key, $previous);
            $inNew = array_key_exists($key, $relevantDiff);
            $prevEntry = $inPrevious && is_array($previous[$key] ?? null) ? $previous[$key] : null;

            if ($inPrevious && $inNew) {
                $newEntry = $relevantDiff[$key];
                $merged[$key] = [
                    'from' => is_array($prevEntry) && array_key_exists('from', $prevEntry)
                        ? $prevEntry['from']
                        : $newEntry['from'],
                    'to' => $newEntry['to'] ?? null,
                ];
            } elseif ($inNew) {
                $merged[$key] = $relevantDiff[$key];
            } else {
                $merged[$key] = is_array($prevEntry) ? $prevEntry : [
                    'from' => null,
                    'to' => null,
                ];
            }
        }

        return Arr::except($merged, PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY);
    }

    private function positionAttributeValuesDiffer(mixed $from, mixed $to): bool
    {
        if ($from instanceof CarbonInterface && $to instanceof CarbonInterface) {
            return ! $from->equalTo($to);
        }

        return $from != $to;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
