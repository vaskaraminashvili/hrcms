<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Models\Publication;
use Carbon\CarbonInterface;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PublicationsImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?Publication
    {
        $year = $this->normalizeYear($row['year'] ?? null);
        $title = $this->requiredTranslatableFromRow($row, 'title');

        if ($year === null || $title === null) {
            return null;
        }

        return new Publication([
            'employee_id' => $this->employeeId,
            'title' => $title,
            'place' => $this->optionalTranslatableFromRow($row, 'venue'),
            'co_authors' => $this->optionalTranslatableFromRow($row, 'authors'),
            'published_at' => $year,
            'page_count' => null,
        ]);
    }

    private function normalizeYear(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        if ($value instanceof CarbonInterface) {
            return $value->year;
        }

        if (is_string($value) && preg_match('/^(\d{4})/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
