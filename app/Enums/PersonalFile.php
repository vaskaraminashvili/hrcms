<?php

namespace App\Enums;

enum PersonalFile: string
{
    case ACADEMIC_POSITION = 'academic_position';
    case EDUCATION = 'education';
    case ACADEMIC_DEGREES = 'academic_degrees';
    case WORK_EXPERIENCE = 'work_experience';
    case SCIENTIFIC_PROJECTS = 'scientific_projects';
    case TRAININGS_SEMINARS = 'trainings_seminars';
    case PUBLICATIONS = 'publications';
    case TEXTBOOKS = 'textbooks';
    case SCIENTIFIC_FORUMS = 'scientific_forums';
    case SCHOLARSHIPS_AWARDS = 'scholarships_awards';
    case FOREIGN_LANGUAGES = 'foreign_languages';
    case COMPUTER_SKILLS = 'computer_skills';

    public function label(): string
    {
        return match ($this) {
            self::ACADEMIC_POSITION => 'აკადემიური თანამდებობა',
            self::EDUCATION => 'განათლება',
            self::ACADEMIC_DEGREES => 'აკადემიური ხარისხები',
            self::WORK_EXPERIENCE => 'სამუშაო გამოცდილება',
            self::SCIENTIFIC_PROJECTS => 'სამეცნიერო პროექტები',
            self::TRAININGS_SEMINARS => 'ტრენინგები, სემინარები',
            self::PUBLICATIONS => 'პუბლიკაციები',
            self::TEXTBOOKS => 'სახელმძღვანელოები',
            self::SCIENTIFIC_FORUMS => 'სამეცნიერო ფორუმებში მონაწილეობა',
            self::SCHOLARSHIPS_AWARDS => 'სტიპენდიები / ჯილდოები / სახელმწიფო პრემიები',
            self::FOREIGN_LANGUAGES => 'უცხოური ენების ფლობის ხარისხი',
            self::COMPUTER_SKILLS => 'კომპიუტერული პროგრამების ფლობის ხარისხი',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public function relationship(): string
    {
        return match ($this) {
            self::ACADEMIC_POSITION => 'academicPositions',
            self::EDUCATION => 'educations',
            self::ACADEMIC_DEGREES => 'academicDegrees',
            self::WORK_EXPERIENCE => 'workExperiences',
            self::SCIENTIFIC_PROJECTS => 'scientificProjects',
            self::TRAININGS_SEMINARS => 'trainingsSeminars',
            self::PUBLICATIONS => 'publications',
            self::TEXTBOOKS => 'textbooks',
            self::SCIENTIFIC_FORUMS => 'scientificForums',
            self::SCHOLARSHIPS_AWARDS => 'scholarshipsAwards',
            self::FOREIGN_LANGUAGES => 'foreignLanguages',
            self::COMPUTER_SKILLS => 'computerSkills',
        };
    }

    public function schemaClass(): string
    {
        return 'App\\Filament\\Resources\\Employees\\Schemas\\PersonalFile\\'.str_replace('_', '', ucwords($this->value, '_')).'Schema';
    }

    public function itemLabelField(): string
    {
        return match ($this) {
            self::ACADEMIC_POSITION => 'title',
            self::EDUCATION => 'institution',
            self::ACADEMIC_DEGREES => 'degree',
            self::WORK_EXPERIENCE => 'institution',
            self::SCIENTIFIC_PROJECTS => 'project_name',
            self::TRAININGS_SEMINARS => 'institution',
            self::PUBLICATIONS => 'title',
            self::TEXTBOOKS => 'title',
            self::SCIENTIFIC_FORUMS => 'title',
            self::SCHOLARSHIPS_AWARDS => 'title',
            self::FOREIGN_LANGUAGES => 'language',
            self::COMPUTER_SKILLS => 'title',
        };
    }

    /**
     * Human-readable repeater item label from nested form state.
     *
     * @param  array<string, mixed>  $state
     */
    public function resolveItemLabelFromState(array $state): ?string
    {

        return match ($this) {
            self::ACADEMIC_POSITION => self::resolveAcademicPositionItemLabel($state),
            self::ACADEMIC_DEGREES => self::resolveAcademicDegreeItemLabelFromState($state),
            self::FOREIGN_LANGUAGES => self::resolveForeignLanguageItemLabelFromState($state),
            default => $this->resolveTranslatableItemLabel($state),
        };
    }

    private static function resolveAcademicDegreeItemLabelFromState(array $state): ?string
    {
        $raw = $state['degree'] ?? null;

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        return AcademicDegree::tryFrom($raw)?->getLabel();
    }

    private static function resolveForeignLanguageItemLabelFromState(array $state): ?string
    {

        $raw = $state['language'] ?? null;

        if (! is_string($raw) || $raw === '') {
            return null;
        }
        $label = $raw.' - '.__('filament.personal_file.foreign_languages.level').': '.LanguageProficiency::tryFrom($state['level'])?->getLabel();

        return $label;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private static function resolveAcademicPositionItemLabel(array $state): ?string
    {
        $raw = $state['title'] ?? null;

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        return AcademicPosition::tryFrom($raw)?->getLabel();
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function resolveTranslatableItemLabel(array $state): ?string
    {
        $field = $this->itemLabelField();
        $value = $state[$field]['ka'] ?? $state[$field]['en'] ?? null;

        return is_string($value) ? $value : null;
    }
}
