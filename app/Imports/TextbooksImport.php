<?php

namespace App\Imports;

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Models\Textbook;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TextbooksImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?Textbook
    {
        $title = $this->string($row['title'] ?? null);

        if ($title === '') {
            return null;
        }

        return new Textbook([
            'employee_id' => $this->employeeId,
            'title' => $this->translatable($title),
            'publisher' => $this->optionalTranslatable($row['publisher'] ?? null),
            'co_authors' => $this->optionalTranslatable($row['co_authors'] ?? null),
            'published_at' => $this->string($row['published_at'] ?? null) ?: null,
            'page_count' => $this->optionalInteger($row['page_count'] ?? null),
        ]);
    }

    private function optionalInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        return null;
    }
}
