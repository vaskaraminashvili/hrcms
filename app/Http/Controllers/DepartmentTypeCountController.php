<?php

namespace App\Http\Controllers;

use App\Enums\DepartmentType;
use App\Filament\Resources\Departments\Fields\DepartmentTextField;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentTypeCountController extends Controller
{
    /**
     * Return a count of each DepartmentType among all descendants of the given department.
     *
     * @return JsonResponse<array<int, array{label: string, count: int, classes: string}>>
     */
    public function show(Department $department): JsonResponse
    {
        $rows = $department->descendants()
            ->whereNotNull('type')
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->get()
            ->map(function (Department $row): array {
                $typeValue = $row->type instanceof \BackedEnum
                    ? $row->type->value
                    : (string) $row->type;

                $enum = DepartmentType::from($typeValue);
                $colorKey = $enum->getColor() ?? 'gray';

                $classes = DepartmentTextField::BADGE_COLOR_CLASSES[$colorKey]
                    ?? DepartmentTextField::BADGE_COLOR_CLASSES['gray'];

                return [
                    'label' => $enum->getLabel(),
                    'count' => (int) $row->total,
                    'classes' => $classes,
                ];
            })
            ->filter(fn (array $item): bool => $item['count'] > 0)
            ->values();

        return response()->json($rows);
    }
}
