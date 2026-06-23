<?php

namespace App\Services;

use App\Enums\AcademicDegree as AcademicDegreeEnum;
use App\Enums\AcademicPosition as AcademicPositionEnum;
use App\Enums\CvLocale;
use App\Enums\Education as EducationDegreeEnum;
use App\Enums\Gender;
use App\Enums\LanguageProficiency;
use App\Enums\PersonalFile;
use App\Models\AcademicDegree;
use App\Models\AcademicPosition;
use App\Models\ComputerSkill;
use App\Models\Education;
use App\Models\Employee;
use App\Models\ForeignLanguage;
use App\Models\OtherDocument;
use App\Models\Publication;
use App\Models\ScholarshipAward;
use App\Models\ScientificForum;
use App\Models\ScientificProject;
use App\Models\Textbook;
use App\Models\TrainingSeminar;
use App\Models\WorkExperience;
use Illuminate\Support\Carbon;

class EmployeeCvService
{
    private string $localeKey = 'ka';

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(Employee $employee, CvLocale $locale): array
    {
        $this->localeKey = $locale->value;

        app()->setLocale($this->localeKey);

        $employee->load([
            'academicPositions',
            'educations',
            'academicDegrees',
            'workExperiences',
            'scientificProjects',
            'trainingsSeminars',
            'publications',
            'textbooks',
            'scientificForums',
            'scholarshipsAwards',
            'foreignLanguages',
            'computerSkills',
            'other',
        ]);
        // dd($this->buildSections($employee, $locale));

        return [
            'localeKey' => $this->localeKey,
            'photoUrl' => $employee->employeeImageUrl(),
            'fullName' => $this->fullName($employee, $locale),
            'contact' => [
                'phone' => $employee->mobile_number,
                'email' => $employee->email,
                'address' => $this->formatAddress($employee, $locale),
            ],
            'birthDate' => $this->formatDate($employee->birth_date),
            'gender' => $this->genderLabel($employee->gender, $locale),
            'sections' => $this->buildSections($employee, $locale),
            'assets' => [
                'logo' => 'https://tsmu.edu/ts/images/logo.png',
                'cvIcon' => 'https://sms.tsmu.edu/hr/cv/img/cv.png',
            ],
        ];
    }

    /**
     * @return list<array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}>
     */
    private function buildSections(Employee $employee, CvLocale $locale): array
    {
        $sections = [];

        foreach (PersonalFile::cases() as $personalFile) {
            $section = match ($personalFile) {
                PersonalFile::ACADEMIC_POSITION => $this->academicPositionSection($employee),
                PersonalFile::EDUCATION => $this->educationSection($employee, $locale),
                PersonalFile::ACADEMIC_DEGREES => $this->academicDegreesSection($employee),
                PersonalFile::WORK_EXPERIENCE => $this->workExperienceSection($employee),
                PersonalFile::SCIENTIFIC_PROJECTS => $this->scientificProjectsSection($employee),
                PersonalFile::TRAININGS_SEMINARS => $this->trainingsSeminarsSection($employee),
                PersonalFile::PUBLICATIONS => $this->publicationsSection($employee),
                PersonalFile::TEXTBOOKS => $this->textbooksSection($employee),
                PersonalFile::SCIENTIFIC_FORUMS => $this->scientificForumsSection($employee),
                PersonalFile::SCHOLARSHIPS_AWARDS => $this->scholarshipsAwardsSection($employee),
                PersonalFile::FOREIGN_LANGUAGES => $this->foreignLanguagesSection($employee),
                PersonalFile::COMPUTER_SKILLS => $this->computerSkillsSection($employee),
                PersonalFile::OTHER => $this->otherSection($employee),
            };

            if ($section !== null) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function academicPositionSection(Employee $employee): ?array
    {
        $entries = $employee->academicPositions
            ->map(fn (AcademicPosition $position): array => $this->entry([
                $this->field(
                    __('filament.personal_file.academic_position.title'),
                    $this->academicPositionTitle($position->title),
                ),
            ]))
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::ACADEMIC_POSITION, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function educationSection(Employee $employee, CvLocale $locale): ?array
    {
        $entries = $employee->educations
            ->map(function (Education $education) use ($employee, $locale): array {
                $periodLine = $this->formatPeriod($education->started_at, $education->ended_at);
                $country = $employee->citizenship;

                $header = $country !== null && $country !== ''
                    ? "{$periodLine} &nbsp; ".__('cv.country').': '.$country
                    : $periodLine;

                return $this->entry([
                    $this->field(null, $header),
                    $this->field(__('cv.faculty'), $this->translatable($education->program, $this->localeKey)),
                    $this->field(__('cv.specialty'), $this->translatable($education->specialty, $this->localeKey)),
                    $this->field(__('cv.qualification'), $this->employeeQualificationLabel($employee, $locale)),
                    $this->field(__('filament.personal_file.education.institution'), $this->translatable($education->institution, $this->localeKey)),
                ]);
            })
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::EDUCATION, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function academicDegreesSection(Employee $employee): ?array
    {
        $entries = $employee->academicDegrees
            ->map(fn (AcademicDegree $degree): array => $this->entry([
                $this->field(__('cv.degree_title'), $this->academicDegreeTitle($degree, $this->localeKey)),
            ]))
            ->all();

        return $this->section(PersonalFile::ACADEMIC_DEGREES, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function workExperienceSection(Employee $employee): ?array
    {
        $entries = $employee->workExperiences
            ->map(fn (WorkExperience $experience): array => $this->entry([
                $this->field(__('cv.period'), $this->formatPeriod($experience->started_at, $experience->ended_at)),
                $this->field(__('filament.personal_file.work_experience.institution'), $this->translatable($experience->institution, $this->localeKey)),
                $this->field(__('filament.personal_file.work_experience.position'), $this->translatable($experience->position, $this->localeKey)),
            ]))
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::WORK_EXPERIENCE, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function scientificProjectsSection(Employee $employee): ?array
    {
        $entries = $employee->scientificProjects
            ->map(fn (ScientificProject $project): array => $this->entry([
                $this->field(__('cv.period'), $this->formatPeriod($project->started_at, $project->ended_at)),
                $this->field(__('cv.project'), $this->translatable($project->project_name, $this->localeKey)),
                $this->field(__('filament.personal_file.scientific_projects.institution'), $this->translatable($project->institution, $this->localeKey)),
                $this->field(__('filament.personal_file.scientific_projects.position'), $this->translatable($project->position, $this->localeKey)),
                $this->field(__('cv.supervisor'), null, alwaysShow: true),
                $this->field(__('cv.donor'), null, alwaysShow: true),
            ]))
            ->all();

        return $this->section(PersonalFile::SCIENTIFIC_PROJECTS, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function trainingsSeminarsSection(Employee $employee): ?array
    {
        $entries = $employee->trainingsSeminars
            ->map(fn (TrainingSeminar $training): array => $this->entry([
                $this->field(__('cv.period'), $this->formatPeriod($training->started_at, $training->ended_at)),
                $this->field(__('filament.personal_file.trainings_seminars.institution'), $this->translatable($training->institution, $this->localeKey)),
                $this->field(__('filament.personal_file.trainings_seminars.topic'), $this->translatable($training->topic, $this->localeKey)),
            ]))
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::TRAININGS_SEMINARS, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function publicationsSection(Employee $employee): ?array
    {
        $entries = $employee->publications
            ->map(fn (Publication $publication): array => $this->entry([
                $this->field(__('filament.personal_file.publications.title'), $this->translatable($publication->title, $this->localeKey)),
                $this->field(__('filament.personal_file.publications.place'), $this->translatable($publication->place, $this->localeKey)),
                $this->field(__('filament.personal_file.publications.co_authors'), $this->translatable($publication->co_authors, $this->localeKey)),
                $this->field(__('filament.personal_file.dates.published_at'), $publication->published_at !== null ? (string) $publication->published_at : null),
                $this->field(__('filament.personal_file.page_count'), $publication->page_count !== null ? (string) $publication->page_count : null),
            ]))
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::PUBLICATIONS, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function textbooksSection(Employee $employee): ?array
    {
        $entries = $employee->textbooks
            ->map(fn (Textbook $textbook): array => $this->entry([
                $this->field(__('filament.personal_file.textbooks.title'), $this->translatable($textbook->title, $this->localeKey)),
                $this->field(__('filament.personal_file.textbooks.publisher'), $this->translatable($textbook->publisher, $this->localeKey)),
                $this->field(__('filament.personal_file.textbooks.co_authors'), $this->translatable($textbook->co_authors, $this->localeKey)),
                $this->field(__('filament.personal_file.dates.published_at'), $textbook->published_at),
                $this->field(__('filament.personal_file.page_count'), $textbook->page_count !== null ? (string) $textbook->page_count : null),
            ]))
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::TEXTBOOKS, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function scientificForumsSection(Employee $employee): ?array
    {
        $entries = $employee->scientificForums
            ->map(fn (ScientificForum $forum): array => $this->entry([
                $this->field(__('filament.personal_file.scientific_forums.title'), $this->translatable($forum->title, $this->localeKey)),
                $this->field(__('filament.personal_file.scientific_forums.participation_form'), $this->translatable($forum->participation_form, $this->localeKey)),
                $this->field(__('cv.period'), $this->formatPeriod(
                    $forum->getAttribute('start_date') ?? $forum->getAttribute('held_at'),
                    $forum->getAttribute('end_date'),
                )),
            ]))
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::SCIENTIFIC_FORUMS, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function scholarshipsAwardsSection(Employee $employee): ?array
    {
        $entries = $employee->scholarshipsAwards
            ->map(fn (ScholarshipAward $award): array => $this->entry([
                $this->field(__('cv.date'), $award->issued_at, alwaysShow: true),
                $this->field(__('cv.scholarship_title'), $this->translatable($award->title, $this->localeKey)),
                $this->field(__('filament.personal_file.scholarships_awards.issuer'), $this->translatable($award->issuer, $this->localeKey), alwaysShow: true),
                $this->field(__('filament.personal_file.scholarships_awards.grant_details'), $this->grantDetails($award)),
            ]))
            ->all();

        return $this->section(PersonalFile::SCHOLARSHIPS_AWARDS, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function foreignLanguagesSection(Employee $employee): ?array
    {
        $entries = $employee->foreignLanguages
            ->map(fn (ForeignLanguage $language): array => $this->entry([
                $this->field(__('filament.personal_file.foreign_languages.language'), $language->language),
                $this->field(__('filament.personal_file.foreign_languages.level'), $this->languageProficiencyLabel($language->level)),
            ]))
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::FOREIGN_LANGUAGES, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function computerSkillsSection(Employee $employee): ?array
    {
        $entries = $employee->computerSkills
            ->map(fn (ComputerSkill $skill): array => $this->entry([
                $this->field(__('filament.personal_file.computer_skills.title'), $this->translatable($skill->title, $this->localeKey)),
                $this->field(__('filament.personal_file.computer_skills.level'), $this->translatable($skill->level, $this->localeKey)),
            ]))
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::COMPUTER_SKILLS, $entries);
    }

    /**
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string, value: string|null}>}>}|null
     */
    private function otherSection(Employee $employee): ?array
    {
        $entries = $employee->other
            ->map(fn (OtherDocument $document): array => $this->entry([
                $this->field(__('filament.personal_file.other.title'), $this->translatable($document->title, $this->localeKey)),
            ]))
            ->filter(fn (array $entry): bool => $entry['fields'] !== [])
            ->values()
            ->all();

        return $this->section(PersonalFile::OTHER, $entries);
    }

    /**
     * @param  list<array{label: string|null, value: string|null, alwaysShow?: bool}|null>  $fields
     * @return array{fields: list<array{label: string|null, value: string|null}>}
     */
    private function entry(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $field) {
            if ($field === null) {
                continue;
            }

            $hasValue = $field['value'] !== null && $field['value'] !== '';
            $alwaysShow = $field['alwaysShow'] ?? false;

            if (! $hasValue && ! $alwaysShow && $field['label'] === null) {
                continue;
            }

            if (! $hasValue && ! $alwaysShow) {
                continue;
            }

            $normalized[] = [
                'label' => $field['label'],
                'value' => $field['value'],
            ];
        }

        return ['fields' => $normalized];
    }

    /**
     * @param  list<array{fields: list<array{label: string|null, value: string|null}>}>  $entries
     * @return array{key: string, title: string, entries: list<array{fields: list<array{label: string|null, value: string|null}>}>}|null
     */
    private function section(PersonalFile $personalFile, array $entries): ?array
    {
        if ($entries === []) {
            return null;
        }

        return [
            'key' => $personalFile->value,
            'title' => __('filament.personal_file.tabs.'.$personalFile->value),
            'entries' => $entries,
        ];
    }

    /**
     * @return array{label: string|null, value: string|null, alwaysShow?: bool}|null
     */
    private function field(?string $label, ?string $value, bool $alwaysShow = false): ?array
    {
        if (($value === null || $value === '') && ! $alwaysShow && $label !== null) {
            return null;
        }

        if (($value === null || $value === '') && ! $alwaysShow && $label === null) {
            return null;
        }

        return [
            'label' => $label,
            'value' => $value,
            'alwaysShow' => $alwaysShow,
        ];
    }

    private function grantDetails(ScholarshipAward $award): ?string
    {
        $raw = $award->getAttributes()['grant_details'] ?? null;

        if ($raw === null) {
            return null;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);

            return is_array($decoded)
                ? $this->translatable($decoded, $this->localeKey)
                : $raw;
        }

        return $this->translatable($raw, $this->localeKey);
    }

    private function academicPositionTitle(?string $title): ?string
    {
        if ($title === null || $title === '') {
            return null;
        }

        $enum = AcademicPositionEnum::tryFrom($title);

        if ($enum !== null) {
            return __('cv.academic_positions.'.strtolower($enum->value));
        }

        return $title;
    }

    private function languageProficiencyLabel(?string $level): ?string
    {
        if ($level === null || $level === '') {
            return null;
        }

        return LanguageProficiency::tryFrom($level)?->getDisplayLabel() ?? $level;
    }

    private function fullName(Employee $employee, CvLocale $locale): string
    {
        if ($locale === CvLocale::English) {
            $name = trim(sprintf('%s %s', $employee->name_eng ?? '', $employee->surrname_eng ?? ''));

            if ($name !== '') {
                return $name;
            }
        }

        return $employee->full_name;
    }

    private function formatAddress(Employee $employee, CvLocale $locale): ?string
    {
        $details = $employee->address_details;

        if (! is_array($details)) {
            return null;
        }

        if ($locale === CvLocale::English) {
            $parts = array_filter([
                $details['en_address_physical'] ?? null,
            ]);
        } else {
            $parts = array_filter([
                $details['address_physical'] ?? null,
            ]);
        }

        if ($parts === []) {
            $parts = array_filter([
                $details['address_physical'] ?? null,
                $details['address_jurisdiction'] ?? null,
            ]);
        }

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function genderLabel(?Gender $gender, CvLocale $locale): ?string
    {
        if ($gender === null) {
            return null;
        }

        return match ($locale) {
            CvLocale::Georgian => match ($gender) {
                Gender::MALE => __('cv.genders.male_ka'),
                Gender::FEMALE => __('cv.genders.female_ka'),
            },
            CvLocale::English => match ($gender) {
                Gender::MALE => __('cv.genders.male_en'),
                Gender::FEMALE => __('cv.genders.female_en'),
            },
        };
    }

    private function academicDegreeTitle(AcademicDegree $degree, string $localeKey): string
    {
        $enum = AcademicDegreeEnum::tryFrom($degree->degree);

        if ($enum === AcademicDegreeEnum::OTHER) {
            $custom = $this->translatable($degree->other, $localeKey);

            return $custom ?? __('cv.academic_degrees.other');
        }

        if ($enum !== null) {
            return __('cv.academic_degrees.'.strtolower($enum->value));
        }

        return (string) $degree->degree;
    }

    private function employeeQualificationLabel(Employee $employee, CvLocale $locale): ?string
    {
        $degree = $employee->degree;

        if ($degree === null || $degree === '') {
            return null;
        }

        $enum = EducationDegreeEnum::tryFrom((string) $degree);

        if ($enum === null) {
            return (string) $degree;
        }

        if ($locale === CvLocale::English) {
            return $enum->value;
        }

        return $enum->getLabel();
    }

    /**
     * @param  array<string, string>|string|null  $value
     */
    private function translatable(array|string|null $value, string $localeKey): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value !== '' ? $value : null;
        }

        $localized = $value[$localeKey] ?? null;

        if (is_string($localized) && trim($localized) !== '') {
            return trim($localized);
        }

        foreach (['ka', 'en'] as $fallback) {
            $candidate = $value[$fallback] ?? null;

            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    private function formatDate(mixed $date): ?string
    {
        if ($date === null) {
            return null;
        }

        if ($date instanceof Carbon) {

            return $date->format('d.m.Y');
        } else {
            $date = Carbon::parse($date);

            return $date->format('d.m.Y');
        }

        return (string) $date;
    }

    private function formatPeriod(mixed $startedAt, mixed $endedAt): string
    {
        $start = $this->formatDate($startedAt) ?? '';
        $end = $this->formatDate($endedAt) ?? '';

        return "{$start} - {$end}";
    }
}
