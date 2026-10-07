<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Imports\Concerns\UpdatesExistingImportRows;
use App\Models\Education;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EducationImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;
    use UpdatesExistingImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?Education
    {
        $institution = $this->requiredTranslatableFromRow($row, 'institution');

        if ($institution === null) {
            return null;
        }

        return $this->modelFromRow(Education::class, $row, [
            'employee_id' => $this->employeeId,
            'institution' => $institution,
            'program' => $this->optionalTranslatableFromRow($row, 'program'),
            'specialty' => $this->optionalTranslatableFromRow($row, 'specialty'),
            'started_at' => $this->optionalDate($row['started_at'] ?? null),
            'ended_at' => $this->optionalDate($row['ended_at'] ?? null),
        ]);
    }
}
