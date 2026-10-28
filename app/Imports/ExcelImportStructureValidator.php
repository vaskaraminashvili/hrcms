<?php

namespace App\Imports;

use App\Exceptions\InvalidExcelImportStructureException;
use App\Exports\FilledExcelTemplate;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportStructureValidator
{
    /**
     * @throws InvalidExcelImportStructureException
     */
    public static function validateAgainstTemplate(string $importPath, string $templatePath, int $optionalTrailingColumns = 0): void
    {
        abort_unless(is_file($templatePath), 404);

        $expectedHeadings = self::normalizeHeadings(self::readHeadingRow($templatePath));
        $actualHeadings = self::withoutLeadingIdentifier(
            self::normalizeHeadings(self::readHeadingRow($importPath)),
        );

        if (! self::headingsMatch($expectedHeadings, $actualHeadings, $optionalTrailingColumns)) {
            throw new InvalidExcelImportStructureException;
        }
    }

    /**
     * @return list<string>
     */
    private static function readHeadingRow(string $path): array
    {
        $rows = Excel::toArray(new class implements ToArray
        {
            public function array(array $array): array
            {
                return $array;
            }
        }, $path);

        $firstRow = $rows[0][0] ?? [];

        return array_map(
            fn (mixed $value): string => trim((string) ($value ?? '')),
            $firstRow,
        );
    }

    /**
     * @param  list<string>  $headings
     * @return list<string>
     */
    private static function normalizeHeadings(array $headings): array
    {
        while ($headings !== [] && end($headings) === '') {
            array_pop($headings);
        }

        return array_values($headings);
    }

    /**
     * Filled exports prefix the template with an id column. Blank templates do not.
     *
     * @param  list<string>  $headings
     * @return list<string>
     */
    private static function withoutLeadingIdentifier(array $headings): array
    {
        if (mb_strtolower($headings[0] ?? '') === FilledExcelTemplate::ID_HEADING) {
            array_shift($headings);
        }

        return array_values($headings);
    }

    /**
     * @param  list<string>  $expected
     * @param  list<string>  $actual
     */
    private static function headingsMatch(array $expected, array $actual, int $optionalTrailingColumns = 0): bool
    {
        if ($optionalTrailingColumns < 0 || $optionalTrailingColumns >= count($expected)) {
            return false;
        }

        $requiredCount = count($expected) - $optionalTrailingColumns;

        if (count($actual) < $requiredCount || count($actual) > count($expected)) {
            return false;
        }

        foreach ($actual as $index => $heading) {
            if (mb_strtolower($heading) !== mb_strtolower($expected[$index] ?? '')) {
                return false;
            }
        }

        return true;
    }
}
