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
        $title = $this->string($row['title'] ?? null);

        if ($title === '') {
            return null;
        }

        return new ScientificForum([
            'employee_id' => $this->employeeId,
            'title' => $this->translatable($title),
            'participation_form' => $this->optionalTranslatable($row['participation_form'] ?? null),
            'held_at' => $this->optionalDate($row['held_at'] ?? null),
        ]);
    }
}
