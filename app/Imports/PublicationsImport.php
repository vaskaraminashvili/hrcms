<?php

namespace App\Imports;

use App\Enums\PublicationScope;
use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Imports\Concerns\UpdatesExistingImportRows;
use App\Models\Publication;
use Carbon\CarbonInterface;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PublicationsImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;
    use UpdatesExistingImportRows;

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

        return $this->modelFromRow(Publication::class, $row, [
            'employee_id' => $this->employeeId,
            'title' => $title,
            'place' => $this->optionalTranslatableFromRow($row, 'venue'),
            'co_authors' => $this->optionalTranslatableFromRow($row, 'authors'),
            'published_at' => $year,
            'publication_details' => $this->publicationDetailsFromRow($row),
        ], [
            'page_count' => null,
        ]);
    }

    /**
     * @return array{scope: string, indexed: bool, impact_factor: bool}
     */
    private function publicationDetailsFromRow(array $row): array
    {
        $scope = $this->geographicScopeFromRow($row);
        $indexed = $scope === PublicationScope::International->value
            && $this->booleanFromRow($row['indexed'] ?? null);

        return [
            'scope' => $scope,
            'indexed' => $indexed,
            'impact_factor' => $indexed && $this->booleanFromRow($row['impact_factor'] ?? null),
        ];
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
