<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Models\ScientificForum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScientificForumsImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?ScientificForum
    {
        $title = $this->requiredTranslatableFromRow($row, 'title');

        if ($title === null) {
            return null;
        }

        return new ScientificForum([
            'employee_id' => $this->employeeId,
            'title' => $title,
            'participation_form' => $this->optionalTranslatableFromRow($row, 'participation_form'),
            'start_date' => $this->optionalDate($row['start_date'] ?? null),
            'end_date' => $this->optionalDate($row['end_date'] ?? null),
        ]);
    }
}
