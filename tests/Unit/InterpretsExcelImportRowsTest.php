<?php

use App\Imports\Concerns\InterpretsExcelImportRows;
use App\Imports\EducationImport;

/**
 * @return object{
 *     translatableFromKaEn: callable,
 *     optionalTranslatableFromRow: callable,
 *     requiredTranslatableFromRow: callable,
 * }
 */
function excelImportRowInterpreter(): object
{
    return new class
    {
        use InterpretsExcelImportRows {
            translatableFromKaEn as public;
            optionalTranslatableFromRow as public;
            requiredTranslatableFromRow as public;
        }
    };
}

test('translatableFromKaEn keeps both locales when provided', function () {
    $interpreter = excelImportRowInterpreter();

    expect($interpreter->translatableFromKaEn('ქართული', 'English'))
        ->toBe(['ka' => 'ქართული', 'en' => 'English']);
});

test('translatableFromKaEn falls back when one locale is empty', function () {
    $interpreter = excelImportRowInterpreter();

    expect($interpreter->translatableFromKaEn('ქართული', ''))
        ->toBe(['ka' => 'ქართული', 'en' => 'ქართული']);

    expect($interpreter->translatableFromKaEn('', 'English'))
        ->toBe(['ka' => 'English', 'en' => 'English']);
});

test('optionalTranslatableFromRow returns null when both locales are empty', function () {
    $interpreter = excelImportRowInterpreter();

    expect($interpreter->optionalTranslatableFromRow([
        'program_ka' => '',
        'program_en' => null,
    ], 'program'))->toBeNull();
});

test('optionalTranslatableFromRow parses bilingual columns from a row', function () {
    $interpreter = excelImportRowInterpreter();

    expect($interpreter->optionalTranslatableFromRow([
        'program_ka' => 'პროგრამა',
        'program_en' => 'Program',
    ], 'program'))->toBe(['ka' => 'პროგრამა', 'en' => 'Program']);
});

test('requiredTranslatableFromRow skips rows without a required value', function () {
    $interpreter = excelImportRowInterpreter();

    expect($interpreter->requiredTranslatableFromRow([
        'institution_ka' => '  ',
        'institution_en' => '',
    ], 'institution'))->toBeNull();
});

test('education import builds bilingual attributes from template columns', function () {
    $import = new EducationImport(1);

    $education = $import->model([
        'institution_ka' => 'უნივერსიტეტი',
        'institution_en' => 'University',
        'program_ka' => 'ფაკულტეტი',
        'program_en' => 'Faculty',
        'specialty_ka' => '',
        'specialty_en' => 'Computer Science',
        'started_at' => '2020-01-01',
        'ended_at' => '2024-06-30',
    ]);

    expect($education)->not->toBeNull()
        ->and($education->getTranslations('institution'))->toBe(['ka' => 'უნივერსიტეტი', 'en' => 'University'])
        ->and($education->getTranslations('program'))->toBe(['ka' => 'ფაკულტეტი', 'en' => 'Faculty'])
        ->and($education->getTranslations('specialty'))->toBe(['ka' => 'Computer Science', 'en' => 'Computer Science'])
        ->and($education->employee_id)->toBe(1);
});

test('education import returns null when required institution is missing', function () {
    $import = new EducationImport(1);

    expect($import->model([
        'institution_ka' => '',
        'institution_en' => '',
        'program_ka' => 'პროგრამა',
        'program_en' => 'Program',
    ]))->toBeNull();
});
