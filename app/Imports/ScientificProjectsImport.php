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
        $projectName = $this->string($row['project_name'] ?? null);

        if ($projectName === '') {
            return null;
        }

        return new ScientificProject([
            'employee_id' => $this->employeeId,
            'project_name' => $this->translatable($projectName),
            'institution' => $this->optionalTranslatable($row['institution'] ?? null),
            'position' => $this->optionalTranslatable($row['position'] ?? null),
            'started_at' => $this->optionalDate($row['started_at'] ?? null),
            'ended_at' => $this->optionalDate($row['ended_at'] ?? null),
        ]);
    }
}
