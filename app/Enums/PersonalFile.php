<?php

namespace App\Enums;

enum PersonalFile: string
{
    case ACADEMIC_POSITION       = 'academic_position';
    case EDUCATION               = 'education';
    case ACADEMIC_DEGREES        = 'academic_degrees';
    case WORK_EXPERIENCE         = 'work_experience';
    case SCIENTIFIC_PROJECTS     = 'scientific_projects';
    case TRAININGS_SEMINARS      = 'trainings_seminars';
    case PUBLICATIONS            = 'publications';
    case TEXTBOOKS               = 'textbooks';
    case SCIENTIFIC_FORUMS       = 'scientific_forums';
    case SCHOLARSHIPS_AWARDS     = 'scholarships_awards';
    case FOREIGN_LANGUAGES       = 'foreign_languages';
    case COMPUTER_SKILLS         = 'computer_skills';

    public function label(): string
    {
        return match($this) {
            self::ACADEMIC_POSITION   => 'აკადემიური თანამდებობა',
            self::EDUCATION           => 'განათლება',
            self::ACADEMIC_DEGREES    => 'აკადემიური ხარისხები',
            self::WORK_EXPERIENCE     => 'სამუშაო გამოცდილება',
            self::SCIENTIFIC_PROJECTS => 'სამეცნიერო პროექტები',
            self::TRAININGS_SEMINARS  => 'ტრენინგები, სემინარები',
            self::PUBLICATIONS        => 'პუბლიკაციები',
            self::TEXTBOOKS           => 'სახელმძღვანელოები',
            self::SCIENTIFIC_FORUMS   => 'სამეცნიერო ფორუმებში მონაწილეობა',
            self::SCHOLARSHIPS_AWARDS => 'სტიპენდიები / ჯილდოები / სახელმწიფო პრემიები',
            self::FOREIGN_LANGUAGES   => 'უცხოური ენების ფლობის ხარისხი',
            self::COMPUTER_SKILLS     => 'კომპიუტერული პროგრამების ფლობის ხარისხი',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
