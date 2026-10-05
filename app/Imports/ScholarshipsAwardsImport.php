<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Imports\Concerns\UpdatesExistingImportRows;
use App\Models\ScholarshipAward;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScholarshipsAwardsImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;
    use UpdatesExistingImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?ScholarshipAward
    {
        $title = $this->requiredTranslatableFromRow($row, 'title');
        $grantDetails = $this->requiredTranslatableFromRow($row, 'grant_details');

        if ($title === null || $grantDetails === null) {
            return null;
        }

        return $this->modelFromRow(ScholarshipAward::class, $row, [
            'employee_id' => $this->employeeId,
            'title' => $title,
            'grant_details' => $grantDetails,
            'issuer' => $this->optionalTranslatableFromRow($row, 'issuer'),
            'issued_at' => $this->string($row['issued_at'] ?? null) ?: null,
        ]);
    }
}
