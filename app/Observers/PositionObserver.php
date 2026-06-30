<?php

namespace App\Observers;

use App\Enums\PositionHistoryAffectField;
use App\Enums\PositionHistorySnapshotField;
use App\Models\Position;
use Illuminate\Support\Arr;

class PositionObserver
{
    public function created(Position $position): void
    {
        $position->histories()->create([
            'changed_by' => auth()->id(),
            'event_type' => 'created',
            'snapshot' => $this->snapshotForHistory($position),
            'changed_fields' => null,
            ...collect(PositionHistoryAffectField::cases())
                ->mapWithKeys(fn (PositionHistoryAffectField $field) => [
                    $field->value => $field->shouldMarkAffectedOnCreate(),
                ])
                ->all(),
        ]);
    }

    public function updated(Position $position): void
    {
        $dirty = $position->getDirty();

        $relevantDirty = Arr::except($dirty, PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY);

        if (empty($relevantDirty)) {
            return;
        }

        $position->histories()->create([
            'changed_by' => auth()->id(),
            'event_type' => 'updated',
            'snapshot' => $this->snapshotForHistory($position),
            'changed_fields' => $this->buildDiff($relevantDirty, $position),
            ...collect(PositionHistoryAffectField::cases())
                ->mapWithKeys(fn (PositionHistoryAffectField $field) => [
                    $field->value => $field->isAffectedByDirty($relevantDirty),
                ])
                ->all(),
        ]);
    }

    public function deleted(Position $position): void
    {
        $position->histories()->create([
            'changed_by' => auth()->id(),
            'event_type' => 'deleted',
            'snapshot' => $this->snapshotForHistory($position),
            'changed_fields' => null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotForHistory(Position $position): array
    {
        return Arr::except($position->toArray(), PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY);
    }

    private function buildDiff(array $dirty, Position $position): array
    {
        $diff = [];

        foreach ($dirty as $field => $newValue) {
            $diff[$field] = [
                'from' => $position->getOriginal($field),
                'to' => $newValue,
            ];
        }

        return $diff;
    }
}
