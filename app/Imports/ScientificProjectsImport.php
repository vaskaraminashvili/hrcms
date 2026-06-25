<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Models\ScientificProject;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScientificProjectsImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?ScientificProject
    {
        $projectName = $this->requiredTranslatableFromRow($row, 'project_name');

        if ($projectName === null) {
            return null;
        }

        return new ScientificProject([
            'employee_id' => $this->employeeId,
            'project_name' => $projectName,
            'institution' => $this->optionalTranslatableFromRow($row, 'institution'),
            'position' => $this->optionalTranslatableFromRow($row, 'position'),
            'started_at' => $this->optionalDate($row['started_at'] ?? null),
            'ended_at' => $this->optionalDate($row['ended_at'] ?? null),
        ]);
    }
}
