<?php

namespace App\Http\Controllers;

use App\Services\EmployeeImportService;
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
}
