<?php

namespace App\Http\Controllers;

use App\Services\EmployeeImportService;
use App\Services\ImageImportService;
use App\Services\PositionImportService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function importEmployees(Request $request, EmployeeImportService $employeeImportService)
    {
        $result = $employeeImportService->importAll(
            clearTableBefore: $request->boolean('clear', true)
        );

        return response()->json($result);
    }

    public function importPositions(Request $request, PositionImportService $positionImportService)
    {
        $result = $positionImportService->importAll(
            clearTableBefore: $request->boolean('clear', true)
        );

        return response()->json($result);
    }

    public function importImages(Request $request, ImageImportService $imageImportService)
    {
        $result = $imageImportService->importAll(
            clearTableBefore: $request->boolean('clear', true)
        );

        return response()->json($result);
    }
}
