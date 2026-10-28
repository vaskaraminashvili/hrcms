<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Imports\Concerns\UpdatesExistingImportRows;
use App\Models\TrainingSeminar;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TrainingsSeminarsImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;
    use UpdatesExistingImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?TrainingSeminar
    {
        $institution = $this->requiredTranslatableFromRow($row, 'institution');

        if ($institution === null) {
            return null;
        }

        return $this->modelFromRow(TrainingSeminar::class, $row, [
            'employee_id' => $this->employeeId,
            'institution' => $institution,
            'topic' => $this->optionalTranslatableFromRow($row, 'topic'),
            'started_at' => $this->optionalDate($row['started_at'] ?? null),
            'ended_at' => $this->optionalDate($row['ended_at'] ?? null),
        ]);
    }
}
