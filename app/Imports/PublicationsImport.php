<?php

namespace App\Imports;

use App\Models\Publication;
use Carbon\CarbonInterface;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PublicationsImport implements ToModel, WithHeadingRow
{
    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?Publication
    {
        $year = $this->normalizeYear($row['year'] ?? null);
        $title = $this->string($row['title'] ?? null);

        if ($year === null || $title === '') {
            return null;
        }

        $authors = $this->string($row['authors'] ?? null);
        $venue = $this->string($row['venue'] ?? null);

        return new Publication([
            'employee_id' => $this->employeeId,
            'title' => $this->translatable($title),
            'place' => $venue !== '' ? $this->translatable($venue) : null,
            'co_authors' => $authors !== '' ? $this->translatable($authors) : null,
            'published_at' => $year,
            'page_count' => null,
        ]);
    }

    /**
     * @return array{ka: string, en: string}
     */
    private function translatable(string $value): array
    {
        return ['ka' => $value, 'en' => $value];
    }

    private function string(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
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
