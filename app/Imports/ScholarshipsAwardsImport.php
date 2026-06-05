<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Models\ScholarshipAward;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScholarshipsAwardsImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?ScholarshipAward
    {
        $title = $this->string($row['title'] ?? null);
        $grantDetails = $this->string($row['grant_details'] ?? null);
        if ($title === '') {
            return null;
        }

        return new ScholarshipAward([
            'employee_id' => $this->employeeId,
            'title' => $this->translatable($title),
            'grant_details' => $this->translatable($grantDetails),
            'issuer' => $this->optionalTranslatable($row['issuer'] ?? null),
            'issued_at' => $this->optionalDate($row['issued_at'] ?? null),
        ]);
    }
}
