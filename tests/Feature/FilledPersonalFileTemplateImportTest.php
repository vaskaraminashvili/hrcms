<?php

use App\Enums\PublicationScope;
use App\Exports\FilledExcelTemplate;
use App\Exports\PersonalFileTemplateRows;
use App\Imports\EducationImport;
use App\Imports\ExcelImportStructureValidator;
use App\Imports\PublicationsImport;
use App\Imports\ScholarshipsAwardsImport;
use App\Imports\ScientificForumsImport;
use App\Imports\TextbooksImport;
use App\Imports\TrainingsSeminarsImport;
use App\Imports\WorkExperienceImport;
use App\Models\Education;
use App\Models\Employee;
use App\Models\Publication;
use App\Models\ScholarshipAward;
use App\Models\ScientificForum;
use App\Models\Textbook;
use App\Models\TrainingSeminar;
use App\Models\WorkExperience;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

uses(LazilyRefreshDatabase::class);

function employeeForFilledTemplate(string $personalNumber): Employee
{
    return Employee::query()->create([
        'name' => 'Nino',
        'surname' => 'Beridze',
        'personal_number' => $personalNumber,
        'birth_date' => '1990-01-01',
    ]);
}

afterEach(function (): void {
    foreach ($GLOBALS['hrcms_filled_import_templates'] ?? [] as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }

    $GLOBALS['hrcms_filled_import_templates'] = [];
});

test('reimporting a filled education file updates the existing row and appends a blank id row', function () {
    $employee = employeeForFilledTemplate('01001001011');
    $other = employeeForFilledTemplate('01001001012');

    $education = Education::factory()->create([
        'employee_id' => $employee->id,
        'institution' => ['ka' => 'ძველი', 'en' => 'Old'],
        'program' => ['ka' => 'პროგრამა', 'en' => 'Program'],
        'specialty' => ['ka' => 'სპეციალობა', 'en' => 'Specialty'],
        'started_at' => '2010-01-01',
        'ended_at' => '2014-01-01',
    ]);

    $foreign = Education::factory()->create([
        'employee_id' => $other->id,
        'institution' => ['ka' => 'სხვისი', 'en' => 'Theirs'],
    ]);

    $path = filledImportPath(resource_path('templates/education/education.xlsx'), [[
        'id' => $education->getKey(),
        ...PersonalFileTemplateRows::education($education),
    ]]);

    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('C2', 'Updated');
    $sheet->setCellValue('A3', $foreign->getKey());
    $sheet->setCellValue('B3', 'ახალი');
    $sheet->setCellValue('C3', 'New');
    $sheet->setCellValue('D3', 'პროგ');
    $sheet->setCellValue('E3', 'Prog');
    IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
    $spreadsheet->disconnectWorksheets();

    Excel::import(new EducationImport($employee->id), $path);

    $education->refresh();

    expect(Education::query()->where('employee_id', $employee->id)->count())->toBe(2)
        ->and($education->getTranslation('institution', 'en'))->toBe('Updated')
        ->and($education->getTranslation('institution', 'ka'))->toBe('ძველი')
        ->and($education->started_at?->toDateString())->toBe('2010-01-01')
        ->and($foreign->refresh()->getTranslation('institution', 'en'))->toBe('Theirs')
        ->and(Education::query()->where('employee_id', $other->id)->count())->toBe(1);

    $created = Education::query()
        ->where('employee_id', $employee->id)
        ->whereKeyNot($education->id)
        ->first();

    expect($created)->not->toBeNull()
        ->and($created->getTranslation('institution', 'en'))->toBe('New');
});

test('reimporting a filled publications file updates the owned row and keeps page count', function () {
    $employee = employeeForFilledTemplate('01001001021');
    $other = employeeForFilledTemplate('01001001022');

    $publication = Publication::factory()->create([
        'employee_id' => $employee->id,
        'title' => ['ka' => 'ძველი', 'en' => 'Old'],
        'place' => ['ka' => 'ჟურნალი', 'en' => 'Journal'],
        'co_authors' => ['ka' => 'ავტორი', 'en' => 'Author'],
        'published_at' => 2020,
        'page_count' => 12,
        'publication_details' => [
            'scope' => PublicationScope::International->value,
            'indexed' => true,
            'impact_factor' => false,
        ],
    ]);

    $foreign = Publication::factory()->create([
        'employee_id' => $other->id,
        'title' => ['ka' => 'სხვისი', 'en' => 'Theirs'],
        'page_count' => 4,
    ]);

    $path = filledImportPath(resource_path('templates/publications/scholar_export.xlsx'), [[
        'id' => $publication->getKey(),
        ...PersonalFileTemplateRows::publication($publication),
    ]]);

    ExcelImportStructureValidator::validateAgainstTemplate(
        $path,
        resource_path('templates/publications/scholar_export.xlsx'),
        optionalTrailingColumns: 3,
    );

    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('D2', 'Updated article');
    $sheet->fromArray([
        null,
        2024,
        'ახალი',
        'New article',
        '',
        '',
        '',
        '',
        'local',
        '0',
        '0',
    ], null, 'A3');
    IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
    $spreadsheet->disconnectWorksheets();

    Excel::import(new PublicationsImport($employee->id), $path);

    $publication->refresh();

    expect(Publication::query()->where('employee_id', $employee->id)->count())->toBe(2)
        ->and($publication->getTranslation('title', 'en'))->toBe('Updated article')
        ->and($publication->page_count)->toBe(12)
        ->and($publication->publication_details['indexed'])->toBeTrue()
        ->and($foreign->refresh()->getTranslation('title', 'en'))->toBe('Theirs')
        ->and($foreign->page_count)->toBe(4);
});

test('filled exports write each section into the matching template columns', function () {
    $employee = employeeForFilledTemplate('01001001031');

    $scholarship = ScholarshipAward::factory()->create([
        'employee_id' => $employee->id,
        'title' => ['ka' => 'გრანტი', 'en' => 'Grant'],
        'issuer' => ['ka' => 'ფონდი', 'en' => 'Fund'],
        'grant_details' => ['ka' => 'დეტალი', 'en' => 'Detail'],
        'issued_at' => '2021',
    ]);

    $forum = ScientificForum::query()->create([
        'employee_id' => $employee->id,
        'title' => ['ka' => 'ფორუმი', 'en' => 'Forum'],
        'participation_form' => ['ka' => 'ზეპირი', 'en' => 'Oral'],
        'participation_role' => 'speaker',
        'scope' => PublicationScope::International->value,
        'start_date' => '2022-05-01',
        'end_date' => '2022-05-03',
    ]);

    $textbook = Textbook::factory()->create([
        'employee_id' => $employee->id,
        'title' => ['ka' => 'წიგნი', 'en' => 'Book'],
        'publisher' => ['ka' => 'გამომცემლობა', 'en' => 'Publisher'],
        'co_authors' => ['ka' => 'თანაავტორი', 'en' => 'Coauthor'],
        'published_at' => '2019',
        'page_count' => 80,
    ]);

    $training = TrainingSeminar::factory()->create([
        'employee_id' => $employee->id,
        'institution' => ['ka' => 'ინსტიტუტი', 'en' => 'Institute'],
        'topic' => ['ka' => 'თემა', 'en' => 'Topic'],
        'started_at' => '2018-02-01',
        'ended_at' => '2018-03-01',
    ]);

    $work = WorkExperience::factory()->create([
        'employee_id' => $employee->id,
        'institution' => ['ka' => 'კომპანია', 'en' => 'Company'],
        'position' => ['ka' => 'თანამდებობა', 'en' => 'Position'],
        'started_at' => '2016-01-01',
        'ended_at' => null,
    ]);

    expect(filledImportRow(
        'templates/scholarships_awards/scholarships_awards.xlsx',
        $scholarship,
        PersonalFileTemplateRows::scholarshipAward(...),
    ))->toMatchArray([
        'id' => (string) $scholarship->getKey(),
        'title_en' => 'Grant',
        'grant_details_ka' => 'დეტალი',
        'issued_at' => '2021',
    ])->and(filledImportRow(
        'templates/scientific_forums/scientific_forums.xlsx',
        $forum,
        PersonalFileTemplateRows::scientificForum(...),
    ))->toMatchArray([
        'id' => (string) $forum->getKey(),
        'title_en' => 'Forum',
        'participation_role' => 'speaker',
        'scope' => 'international',
        'start_date' => '2022-05-01',
    ])->and(filledImportRow(
        'templates/textbooks/textbooks.xlsx',
        $textbook,
        PersonalFileTemplateRows::textbook(...),
    ))->toMatchArray([
        'id' => (string) $textbook->getKey(),
        'title_ka' => 'წიგნი',
        'published_at' => '2019',
        'page_count' => '80',
    ])->and(filledImportRow(
        'templates/trainings_seminars/trainings_seminars.xlsx',
        $training,
        PersonalFileTemplateRows::trainingSeminar(...),
    ))->toMatchArray([
        'id' => (string) $training->getKey(),
        'topic_en' => 'Topic',
        'started_at' => '2018-02-01',
    ])->and(filledImportRow(
        'templates/work_experience/work_experience.xlsx',
        $work,
        PersonalFileTemplateRows::workExperience(...),
    ))->toMatchArray([
        'id' => (string) $work->getKey(),
        'position_en' => 'Position',
        'started_at' => '2016-01-01',
        'ended_at' => null,
    ]);

    Excel::import(new ScholarshipsAwardsImport($employee->id), filledImportPath(
        resource_path('templates/scholarships_awards/scholarships_awards.xlsx'),
        [[
            'id' => $scholarship->getKey(),
            ...PersonalFileTemplateRows::scholarshipAward($scholarship),
            'issuer_en' => 'Updated fund',
        ]],
    ));
    Excel::import(new ScientificForumsImport($employee->id), filledImportPath(
        resource_path('templates/scientific_forums/scientific_forums.xlsx'),
        [[
            'id' => $forum->getKey(),
            ...PersonalFileTemplateRows::scientificForum($forum),
            'participation_role' => 'attendee',
        ]],
    ));
    Excel::import(new TextbooksImport($employee->id), filledImportPath(
        resource_path('templates/textbooks/textbooks.xlsx'),
        [[
            'id' => $textbook->getKey(),
            ...PersonalFileTemplateRows::textbook($textbook),
            'page_count' => 90,
        ]],
    ));
    Excel::import(new TrainingsSeminarsImport($employee->id), filledImportPath(
        resource_path('templates/trainings_seminars/trainings_seminars.xlsx'),
        [[
            'id' => $training->getKey(),
            ...PersonalFileTemplateRows::trainingSeminar($training),
            'topic_en' => 'Updated topic',
        ]],
    ));
    Excel::import(new WorkExperienceImport($employee->id), filledImportPath(
        resource_path('templates/work_experience/work_experience.xlsx'),
        [[
            'id' => $work->getKey(),
            ...PersonalFileTemplateRows::workExperience($work),
            'position_en' => 'Updated position',
        ]],
    ));

    expect($scholarship->refresh()->getTranslation('issuer', 'en'))->toBe('Updated fund')
        ->and(ScholarshipAward::query()->where('employee_id', $employee->id)->count())->toBe(1)
        ->and($forum->refresh()->participation_role)->toBe('attendee')
        ->and(ScientificForum::query()->where('employee_id', $employee->id)->count())->toBe(1)
        ->and($textbook->refresh()->page_count)->toBe(90)
        ->and(Textbook::query()->where('employee_id', $employee->id)->count())->toBe(1)
        ->and($training->refresh()->getTranslation('topic', 'en'))->toBe('Updated topic')
        ->and(TrainingSeminar::query()->where('employee_id', $employee->id)->count())->toBe(1)
        ->and($work->refresh()->getTranslation('position', 'en'))->toBe('Updated position')
        ->and($work->ended_at)->toBeNull()
        ->and(WorkExperience::query()->where('employee_id', $employee->id)->count())->toBe(1);
});

/**
 * @param  callable(Model): array<string, mixed>  $mapRow
 * @return array<string, mixed>
 */
function filledImportRow(string $relativeTemplate, Model $record, callable $mapRow): array
{
    $path = filledImportPath(resource_path($relativeTemplate), [[
        'id' => $record->getKey(),
        ...$mapRow($record),
    ]]);

    $rows = filledImportSheet($path);
    $headings = array_map(
        fn (mixed $value): string => trim((string) ($value ?? '')),
        $rows[0] ?? [],
    );
    $values = array_slice($rows[1] ?? [], 0, count($headings));

    return array_combine($headings, $values);
}

/**
 * @param  iterable<int, array<string, mixed>>  $rows
 */
function filledImportPath(string $templatePath, iterable $rows): string
{
    $path = FilledExcelTemplate::write($templatePath, $rows);
    $GLOBALS['hrcms_filled_import_templates'][] = $path;

    return $path;
}

/**
 * @return list<list<mixed>>
 */
function filledImportSheet(string $path): array
{
    $rows = Excel::toArray(new class implements ToArray
    {
        public function array(array $array): array
        {
            return $array;
        }
    }, $path);

    return $rows[0] ?? [];
}
