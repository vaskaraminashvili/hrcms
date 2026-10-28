<?php

use App\Exports\FilledExcelTemplate;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

afterEach(function (): void {
    foreach ($GLOBALS['hrcms_filled_templates'] ?? [] as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }

    $GLOBALS['hrcms_filled_templates'] = [];
});

test('filled templates keep the blank template columns and prefix them with id', function (string $relative) {
    $template = resource_path('templates/'.$relative);
    $path = rememberFilledTemplate(FilledExcelTemplate::write($template, [
        [FilledExcelTemplate::ID_HEADING => 15, 'institution_ka' => 'ignored-unless-present'],
    ]));

    expect(filledTemplateHeadings($path))->toBe([
        'id',
        ...filledTemplateHeadings($template),
    ]);
})->with([
    'education/education.xlsx',
    'publications/scholar_export.xlsx',
    'scholarships_awards/scholarships_awards.xlsx',
    'scientific_forums/scientific_forums.xlsx',
    'textbooks/textbooks.xlsx',
    'trainings_seminars/trainings_seminars.xlsx',
    'work_experience/work_experience.xlsx',
]);

function rememberFilledTemplate(string $path): string
{
    $GLOBALS['hrcms_filled_templates'][] = $path;

    return $path;
}

/**
 * @return list<string>
 */
function filledTemplateHeadings(string $path): array
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
