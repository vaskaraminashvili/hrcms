<?php

namespace App\Services;

use App\Enums\PositionType;
use App\Models\Employee;
use App\Models\Position;
use App\Models\PositionDetail;
use App\Models\VacationPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class PositionFormPersistence
{
    /**
     * @return array<string, mixed>
     */
    public static function extractPositionData(array $data): array
    {
        return Arr::only($data, ['place_id', 'employee_id', 'department_id']);
    }

    /**
     * @return array<string, mixed>
     */
    public static function extractDetailData(array $data): array
    {
        return Arr::only($data, (new PositionDetail)->getFillable());
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
        $detailData = self::extractDetailData($data);
        $positionData = self::extractPositionData($data);

        $position = new Position($positionData);

        if ($parentEmployee instanceof Employee) {
            $parentEmployee->positions()->save($position);
        } else {
            $position->save();
        }

        $position->detail()->create($detailData);

        return $position->fresh(['detail']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function updatePositionAndDetail(Position $position, array $data): void
    {
        $position->update(self::extractPositionData($data));
        $position->detail()->updateOrCreate(
            ['position_id' => $position->id],
            self::extractDetailData($data),
        );
    }
}
