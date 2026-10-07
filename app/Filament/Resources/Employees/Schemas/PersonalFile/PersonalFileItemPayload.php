<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

final class PersonalFileItemPayload
{
    /**
     * A repeater row with no entered values is not stored.
     * `sort` is assigned by the repeater and does not count as content.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public static function withoutBlankItem(array $data, bool $creating = false): ?array
    {
        $fields = array_diff_key($data, ['sort' => true]);

        if ($fields === [] && ! $creating) {
            return $data;
        }

        return self::hasContent($fields) ? $data : null;
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private static function hasContent(array $fields): bool
    {
        foreach ($fields as $value) {
            if (self::filled($value)) {
                return true;
            }
        }

        return false;
    }

    private static function filled(mixed $value): bool
    {
        if (! is_array($value)) {
            return filled($value);
        }

        foreach ($value as $item) {
            if (self::filled($item)) {
                return true;
            }
        }

        return false;
    }
}
