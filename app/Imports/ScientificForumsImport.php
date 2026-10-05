<?php

namespace App\Imports;

use App\Enums\ScientificForumRole;
use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Imports\Concerns\UpdatesExistingImportRows;
use App\Models\ScientificForum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScientificForumsImport implements ToModel, WithHeadingRow
{
    use InterpretsExcelImportRows;
    use UpdatesExistingImportRows;

    public function __construct(
        private readonly int $employeeId,
    ) {}

    public function model(array $row): ?ScientificForum
    {
        $title = $this->requiredTranslatableFromRow($row, 'title');

        if ($title === null) {
            return null;
        }

        return $this->modelFromRow(ScientificForum::class, $row, [
            'employee_id' => $this->employeeId,
            'title' => $title,
            'participation_form' => $this->optionalTranslatableFromRow($row, 'participation_form'),
            'participation_role' => $this->participationRoleFromRow($row),
            'scope' => $this->geographicScopeFromRow($row),
            'start_date' => $this->optionalDate($row['start_date'] ?? null),
            'end_date' => $this->optionalDate($row['end_date'] ?? null),
        ]);
    }

    private function participationRoleFromRow(array $row): ?string
    {
        $value = $this->string($row['participation_role'] ?? null);

        if ($value === '') {
            return null;
        }

        $normalized = mb_strtolower($value);

        return match ($normalized) {
            ScientificForumRole::Attendee->value, 'დამსწრე' => ScientificForumRole::Attendee->value,
            ScientificForumRole::Speaker->value, 'მომხსენებელი' => ScientificForumRole::Speaker->value,
            ScientificForumRole::Other->value, 'სხვა' => null,
            default => $value,
        };
    }
}
