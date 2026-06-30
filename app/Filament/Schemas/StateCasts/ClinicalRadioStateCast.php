<?php

namespace App\Filament\Schemas\StateCasts;

use BackedEnum;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

/**
 * Maps the positions.clinical boolean to radio values where
 * '0' = clinical and '1' = non-clinical (see PositionHistorySnapshotField).
 */
final class ClinicalRadioStateCast implements StateCast
{
    public function get(mixed $state): mixed
    {
        if ($state === null || $state === '') {
            return null;
        }

        return $state === '0';
    }

    public function set(mixed $state): ?string
    {
        if ($state === null) {
            return null;
        }

        if ($state instanceof BackedEnum) {
            $state = $state->value;
        }

        return match (true) {
            $state === true, $state === 1, $state === '1' => '0',
            default => '1',
        };
    }
}
