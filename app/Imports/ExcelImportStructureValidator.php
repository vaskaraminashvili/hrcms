<?php

namespace App\Imports;

use App\Exceptions\InvalidExcelImportStructureException;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportStructureValidator
{
    /**
     * @throws InvalidExcelImportStructureException
     */
    public static function validateAgainstTemplate(string $importPath, string $templatePath): void
    {
        abort_unless(is_file($templatePath), 404);

        $expectedHeadings = self::normalizeHeadings(self::readHeadingRow($templatePath));
        $actualHeadings = self::normalizeHeadings(self::readHeadingRow($importPath));

        if (! self::headingsMatch($expectedHeadings, $actualHeadings)) {
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
     * @param  list<string>  $expected
     * @param  list<string>  $actual
     */
    private static function headingsMatch(array $expected, array $actual): bool
    {
        if (count($expected) !== count($actual)) {
            return false;
        }

        foreach ($expected as $index => $heading) {
            if (mb_strtolower($heading) !== mb_strtolower($actual[$index] ?? '')) {
                return false;
            }
        }

        return true;
    }
}
