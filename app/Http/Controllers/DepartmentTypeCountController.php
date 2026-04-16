<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\DepartmentDescendantTypeCountService;
use Illuminate\Http\JsonResponse;

class DepartmentTypeCountController extends Controller
{
    /**
     * Return a count of each DepartmentType among all descendants of the given department.
     *
     * @return JsonResponse<array<int, array{label: string, count: int, classes: string}>>
     */
    public function show(Department $department, DepartmentDescendantTypeCountService $departmentDescendantTypeCountService): JsonResponse
    {
        $rows = $departmentDescendantTypeCountService->getCachedDescendantTypeCountsPayload($department);

        return response()->json($rows);
    }
}
