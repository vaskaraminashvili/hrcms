<?php

namespace App\Services;

use App\Enums\PositionType;
use App\Models\Employee;
use App\Models\Position;
use App\Models\VacationPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class PositionFormPersistence
{
    /**
     * @return array<string, mixed>
     */
    public static function extractFillableData(array $data): array
    {
        return Arr::only($data, (new Position)->getFillable());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function prepareDataForCreate(array $data): array
    {
        $positionType = $data['position_type'] ?? null;
        $typeValue = $positionType instanceof PositionType
            ? $positionType->value
            : (string) $positionType;

        $data['vacation_policy_id'] = VacationPolicy::query()
            ->where('position_type', $typeValue)
            ->firstOrFail()
            ->id;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function createFromValidatedData(array $data, ?Model $parentEmployee = null): Position
    {
        $fillable = self::extractFillableData(self::prepareDataForCreate($data));

        $position = new Position($fillable);

        if ($parentEmployee instanceof Employee) {
            $parentEmployee->positions()->save($position);
        } else {
            $position->save();
        }

        return $position->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function updatePositionAndDetail(Position $position, array $data): void
    {
        $position->update(self::extractFillableData($data));
    }
}
