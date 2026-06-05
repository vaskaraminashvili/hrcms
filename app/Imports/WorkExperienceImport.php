<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Models\WorkExperience;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WorkExperienceImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?WorkExperience
    {
        $institution = $this->string($row['institution'] ?? null);
        $position = $this->string($row['position'] ?? null);
        if ($institution === '' || $position === '') {
            return null;
        }

        return new WorkExperience([
            'employee_id' => $this->employeeId,
            'institution' => $this->translatable($institution),
            'position' => $this->translatable($position),
            'started_at' => $this->optionalDate($row['started_at'] ?? null),
            'ended_at' => $this->optionalDate($row['ended_at'] ?? null),
        ]);
    }
}
