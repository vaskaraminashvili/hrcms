<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\AcademicPositionImportService;
use App\Services\ComputerSkillImportService;
use App\Services\EducationImportService;
use App\Services\EmployeeImageImportService;
use App\Services\EmployeeImportService;
use App\Services\ForeignLanguageImportService;
use App\Services\ImageImportService;
use App\Services\PositionImportService;
use App\Services\ScholarshipAwardImportService;
use App\Services\ScientificProjectImportService;
use App\Services\WorkExperienceImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    public function importWorkExperiences(Request $request, WorkExperienceImportService $workExperienceImportService)
    {
        $result = $workExperienceImportService->importAll(
            clearTableBefore: $request->boolean('clear', true)
        );

        return response()->json($result);
    }

    public function importEmployeesPhotos(EmployeeImageImportService $employeeImageImportService)
    {
        $result = $employeeImageImportService->importAll();

        return response()->json($result);
    }

    public function importEducation(Request $request, EducationImportService $educationImportService)
    {
        $result = $educationImportService->importAll();

        return response()->json($result);
    }

    public function mapDepartments()
    {
        $departments = Department::all()->keyBy('id');
        $importDepartments = DB::table('import_departments')->get();
        $importNamesById = $importDepartments->pluck('sax_geo', 'id');

        $mappedCount = 0;

        foreach ($departments as $department) {
            $parentChain = $this->departmentParentChain($department, $departments);

            $candidates = $importDepartments->where('sax_geo', $department->name);
            $importDepartment = $this->resolveImportDepartment($candidates, $parentChain, $importNamesById);

            if ($importDepartment === null) {
                continue;
            }

            DB::table('import_departments')->where('id', $importDepartment->id)->update([
                'imported_id' => $department->id,
            ]);

            $mappedCount++;
        }

        return response()->json([
            'mapped' => $mappedCount,
            'total_departments' => $departments->count(),
        ]);
    }

    /**
     * @param  Collection<int, Department>  $departments
     * @return list<string>
     */
    private function departmentParentChain(Department $department, $departments): array
    {
        $names = [];
        $current = $department;

        while ($current->parent_id !== null) {
            $parent = $departments->get($current->parent_id);
            if ($parent === null) {
                break;
            }

            array_unshift($names, $parent->name);
            $current = $parent;
        }

        return $names;
    }

    /**
     * @param  Collection<int, string>  $importNamesById
     * @return list<string>
     */
    private function importParentChain(object $importRow, $importNamesById): array
    {
        $names = [];

        foreach (range(1, 6) as $level) {
            $ancestorId = (int) $importRow->{'d'.$level};

            if ($ancestorId <= 0 || $ancestorId === (int) $importRow->id) {
                continue;
            }

            $name = $importNamesById->get($ancestorId);
            if ($name !== null) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @param  Collection<int, object>  $candidates
     * @param  list<string>  $parentChain
     * @param  Collection<int, string>  $importNamesById
     */
    private function resolveImportDepartment($candidates, array $parentChain, $importNamesById): ?object
    {
        if ($candidates->isEmpty()) {
            return null;
        }

        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        $byParentChain = $candidates->filter(
            fn (object $importRow): bool => $this->importParentChain($importRow, $importNamesById) === $parentChain
        );

        if ($byParentChain->count() === 1) {
            return $byParentChain->first();
        }

        $directParent = $parentChain !== [] ? $parentChain[array_key_last($parentChain)] : null;

        $byDirectParent = $candidates->filter(function (object $importRow) use ($importNamesById, $directParent): bool {
            $importParents = $this->importParentChain($importRow, $importNamesById);
            $importDirectParent = $importParents !== [] ? $importParents[array_key_last($importParents)] : null;

            return $importDirectParent === $directParent;
        });

        if ($byDirectParent->count() === 1) {
            return $byDirectParent->first();
        }

        return null;
    }
}
