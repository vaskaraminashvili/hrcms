<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Imports\Concerns\UpdatesExistingImportRows;
use App\Models\WorkExperience;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WorkExperienceImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;
    use UpdatesExistingImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?WorkExperience
    {
        $institution = $this->requiredTranslatableFromRow($row, 'institution');
        $position = $this->requiredTranslatableFromRow($row, 'position');

        if ($institution === null || $position === null) {
            return null;
        }

        return $this->modelFromRow(WorkExperience::class, $row, [
            'employee_id' => $this->employeeId,
            'institution' => $institution,
            'position' => $position,
            'started_at' => $this->optionalDate($row['started_at'] ?? null),
            'ended_at' => $this->optionalDate($row['ended_at'] ?? null),
        ]);
    }
}
