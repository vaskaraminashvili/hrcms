<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Models\Education;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EducationImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?Education
    {
        $institution = $this->string($row['institution'] ?? null);

        if ($institution === '') {
            return null;
        }

        return new Education([
            'employee_id' => $this->employeeId,
            'institution' => $this->translatable($institution),
            'program' => $this->optionalTranslatable($row['program'] ?? null),
            'specialty' => $this->optionalTranslatable($row['specialty'] ?? null),
            'started_at' => $this->optionalDate($row['started_at'] ?? null),
            'ended_at' => $this->optionalDate($row['ended_at'] ?? null),
        ]);
    }
}
