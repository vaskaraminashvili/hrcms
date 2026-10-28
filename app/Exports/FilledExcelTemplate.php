<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FilledExcelTemplate
{
    public const ID_HEADING = 'id';

    /**
     * @param  iterable<int, Model>  $records
     * @param  callable(Model): array<string, mixed>  $mapRow
     */
    public static function download(string $templatePath, string $downloadName, iterable $records, callable $mapRow): BinaryFileResponse
    {
        $rows = [];

        foreach ($records as $record) {
            if (! $record instanceof Model) {
                continue;
            }

            $rows[] = [
                ...$mapRow($record),
                self::ID_HEADING => $record->getKey(),
            ];
        }

        return response()
            ->download(self::write($templatePath, $rows), $downloadName)
            ->deleteFileAfterSend(true);
    }

    /**
     * @param  iterable<int, array<string, mixed>>  $rows
     */
    public static function write(string $templatePath, iterable $rows): string
    {
        abort_unless(is_file($templatePath), 404);

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();
        $headings = self::headings($sheet);

        $sheet->insertNewColumnBefore('A');
        $sheet->setCellValue('A1', self::ID_HEADING);
        $sheet->duplicateStyle($sheet->getStyle('B1'), 'A1');
        $sheet->getColumnDimension('A')->setWidth(12);

        $rowIndex = 2;

        foreach ($rows as $row) {
            self::writeCell($sheet, 'A'.$rowIndex, $row[self::ID_HEADING] ?? null);

            foreach ($headings as $index => $heading) {
                $column = Coordinate::stringFromColumnIndex($index + 2);
                self::writeCell($sheet, $column.$rowIndex, $row[$heading] ?? null);
            }

            $rowIndex++;
        }

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'hrcms-filled-'.uniqid('', true).'.xlsx';

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }

    private static function writeCell(Worksheet $sheet, string $coordinate, mixed $value): void
    {
        if ($value === null || $value === '') {
            $sheet->setCellValue($coordinate, null);

            return;
        }

        $sheet->setCellValueExplicit($coordinate, (string) $value, DataType::TYPE_STRING);
    }

    /**
     * @return list<string>
     */
    private static function headings(Worksheet $sheet): array
    {
        $highest = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $headings = [];

        for ($index = 1; $index <= $highest; $index++) {
            $headings[] = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($index).'1')->getValue());
        }

        while ($headings !== [] && end($headings) === '') {
            array_pop($headings);
        }

        return array_values($headings);
    }
}
