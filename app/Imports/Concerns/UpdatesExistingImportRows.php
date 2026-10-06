<?php

namespace App\Imports\Concerns;

use App\Exports\FilledExcelTemplate;
use Illuminate\Database\Eloquent\Model;

trait UpdatesExistingImportRows
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $createOnlyAttributes
     */
    protected function modelFromRow(string $modelClass, array $row, array $attributes, array $createOnlyAttributes = []): Model
    {
        $existing = $this->existingOwnedRecord($modelClass, $row);

        if ($existing instanceof Model) {
            $existing->fill($attributes);

            return $existing;
        }

        return new $modelClass([
            ...$attributes,
            ...$createOnlyAttributes,
        ]);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $row
     */
    protected function existingOwnedRecord(string $modelClass, array $row): ?Model
    {
        $id = $this->importRowId($row);

        if ($id === null) {
            return null;
        }

        return $modelClass::query()
            ->whereKey($id)
            ->where('employee_id', $this->employeeId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function importRowId(array $row): ?int
    {
        $value = $row[FilledExcelTemplate::ID_HEADING] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $id = (int) round((float) $value);

        return $id > 0 ? $id : null;
    }
}
