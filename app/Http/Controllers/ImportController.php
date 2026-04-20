<?php

namespace App\Http\Controllers;

use App\Services\AcademicPositionImportService;
use App\Services\ComputerSkillImportService;
use App\Services\EmployeeImportService;
use App\Services\ForeignLanguageImportService;
use App\Services\ImageImportService;
use App\Services\PositionImportService;
use App\Services\ScholarshipAwardImportService;
use App\Services\ScientificProjectImportService;
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

    public function importComputerSkills(Request $request, ComputerSkillImportService $computerSkillImportService)
    {
        $result = $computerSkillImportService->importAll(
            clearTableBefore: $request->boolean('clear', true)
        );

        return response()->json($result);
    }

    public function importProjects(Request $request, ScientificProjectImportService $scientificProjectImportService)
    {
        $result = $scientificProjectImportService->importAll(
            clearTableBefore: $request->boolean('clear', true)
        );

        return response()->json($result);
    }

    public function importAwards(Request $request, ScholarshipAwardImportService $scholarshipAwardImportService)
    {
        $result = $scholarshipAwardImportService->importAll(
            clearTableBefore: $request->boolean('clear', true)
        );

        return response()->json($result);
    }

    public function importAcademicPositions(Request $request, AcademicPositionImportService $academicPositionImportService)
    {
        $result = $academicPositionImportService->importAll(
            clearTableBefore: $request->boolean('clear', true)
        );

        return response()->json($result);
    }

    public function importLanguages(Request $request, ForeignLanguageImportService $foreignLanguageImportService)
    {
        $result = $foreignLanguageImportService->importAll(
            clearTableBefore: $request->boolean('clear', true)
        );

        return response()->json($result);
    }
}
