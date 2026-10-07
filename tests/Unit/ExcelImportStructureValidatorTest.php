<?php

use App\Exceptions\InvalidExcelImportStructureException;
use App\Imports\ExcelImportStructureValidator;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

afterEach(function (): void {
    cleanupSpreadsheetFixtures();
});

test('validator accepts a file that matches the template headings', function () {
    $template = spreadsheetWithHeadings(['year', 'title_ka', 'title_en']);
    $import = spreadsheetWithHeadings(['year', 'title_ka', 'title_en']);

    ExcelImportStructureValidator::validateAgainstTemplate($import, $template);

    expect(true)->toBeTrue();
});

test('validator rejects a file with a different heading layout', function () {
    $template = spreadsheetWithHeadings(['year', 'title_ka', 'title_en']);
    $import = spreadsheetWithHeadings(['year', 'title_en', 'title_ka']);

    ExcelImportStructureValidator::validateAgainstTemplate($import, $template);
})->throws(InvalidExcelImportStructureException::class);

test('validator allows omitting optional trailing columns from the publications template', function () {
    $template = spreadsheetWithHeadings([
        'year', 'title_ka', 'title_en', 'authors_ka', 'authors_en', 'venue_ka', 'venue_en',
        'scope', 'indexed', 'impact_factor',
    ]);
    $scholarExport = spreadsheetWithHeadings([
        'year', 'title_ka', 'title_en', 'authors_ka', 'authors_en', 'venue_ka', 'venue_en',
    ]);

    ExcelImportStructureValidator::validateAgainstTemplate($scholarExport, $template, optionalTrailingColumns: 3);

    expect(true)->toBeTrue();
});

test('validator accepts a leading id column on a filled export', function () {
    $template = spreadsheetWithHeadings([
        'year', 'title_ka', 'title_en', 'authors_ka', 'authors_en', 'venue_ka', 'venue_en',
        'scope', 'indexed', 'impact_factor',
    ]);
    $import = spreadsheetWithHeadings([
        'id', 'year', 'title_ka', 'title_en', 'authors_ka', 'authors_en', 'venue_ka', 'venue_en',
        'scope', 'indexed', 'impact_factor',
    ]);

    ExcelImportStructureValidator::validateAgainstTemplate($import, $template, optionalTrailingColumns: 3);

    expect(true)->toBeTrue();
});

test('validator still requires the core scholar export columns', function () {
    $template = spreadsheetWithHeadings([
        'year', 'title_ka', 'title_en', 'authors_ka', 'authors_en', 'venue_ka', 'venue_en',
        'scope', 'indexed', 'impact_factor',
    ]);
    $import = spreadsheetWithHeadings(['year', 'title_ka']);

    ExcelImportStructureValidator::validateAgainstTemplate($import, $template, optionalTrailingColumns: 3);
})->throws(InvalidExcelImportStructureException::class);

test('publications template includes optional scope columns after the scholar export layout', function () {
    $headings = headingRowFrom(resource_path('templates/publications/scholar_export.xlsx'));

    expect($headings)->toBe([
        'year',
        'title_ka',
        'title_en',
        'authors_ka',
        'authors_en',
        'venue_ka',
        'venue_en',
        'scope',
        'indexed',
        'impact_factor',
    ]);
});

test('scientific forums template includes participation role and scope columns', function () {
    $headings = headingRowFrom(resource_path('templates/scientific_forums/scientific_forums.xlsx'));

    expect($headings)->toContain('participation_role')
        ->and($headings)->toContain('scope')
        ->and($headings[0] ?? null)->toBe('title_ka');
});

/**
 * @param  list<string>  $headings
 */
function spreadsheetWithHeadings(array $headings): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();

    foreach ($headings as $index => $heading) {
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).'1', $heading);
    }

    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'hrcms-excel-'.uniqid('', true).'.xlsx';
    (new Xlsx($spreadsheet))->save($path);
    $spreadsheet->disconnectWorksheets();

    $GLOBALS['hrcms_excel_fixtures'][] = $path;

    return $path;
}

function headingRowFrom(string $path): array
{
    $rows = Excel::toArray(new class implements ToArray
    {
        public function array(array $array): array
        {
            return $array;
        }
    }, $path);

    $headings = array_map(
        fn (mixed $value): string => trim((string) ($value ?? '')),
        $rows[0][0] ?? [],
    );

    while ($headings !== [] && end($headings) === '') {
        array_pop($headings);
    }

    return array_values($headings);
}

function cleanupSpreadsheetFixtures(): void
{
    foreach ($GLOBALS['hrcms_excel_fixtures'] ?? [] as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }

    $GLOBALS['hrcms_excel_fixtures'] = [];
}
