<?php

namespace App\Observers;

use App\Models\Position;

class PositionObserver
{
    public function created(Position $position): void
    {
        $position->histories()->create([
            'changed_by' => auth()->id(),
            'event_type' => 'created',
            'snapshot' => $position->toArray(),
            'changed_fields' => null,
            'affects_salary' => true,
            'affects_status' => true,
            'affects_position_type' => true,
            'affects_staff_type' => true,
            'affects_date_start' => true,
            'affects_date_end' => true,
            'affects_clinical' => true,
            'affects_vacation_policy' => true,
            'affects_place' => true,
            'affects_act_number' => true,
        ]);
    }

    public function updated(Position $position): void
    {
        $dirty = $position->getDirty();

        if (empty($dirty)) {
            return;
        }

        $position->histories()->create([
            'changed_by' => auth()->id(),
            'event_type' => 'updated',
            'snapshot' => $position->toArray(),
            'changed_fields' => $this->buildDiff($dirty, $position),
            'affects_salary' => isset($dirty['salary']),
            'affects_status' => isset($dirty['status']),
            'affects_position_type' => isset($dirty['position_type']),
            'affects_staff_type' => isset($dirty['staff_type']),
            'affects_date_start' => isset($dirty['date_start']),
            'affects_date_end' => isset($dirty['date_end']),
            'affects_clinical' => isset($dirty['clinical']),
            'affects_vacation_policy' => isset($dirty['vacation_policy_id']),
            'affects_place' => isset($dirty['place_id']),
            'affects_act_number' => isset($dirty['act_number']),
        ]);
    }

    public function deleted(Position $position): void
    {
        $position->histories()->create([
            'changed_by' => auth()->id(),
            'event_type' => 'deleted',
            'snapshot' => $position->toArray(),
            'changed_fields' => null,
        ]);
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
