<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ScientificForumRole: string implements HasLabel
{
    case Attendee = 'attendee';
    case Speaker = 'speaker';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Attendee => __('filament.personal_file.scientific_forums.participation_role_options.attendee'),
            self::Speaker => __('filament.personal_file.scientific_forums.participation_role_options.speaker'),
            self::Other => __('filament.personal_file.scientific_forums.participation_role_options.other'),
        };
    }
}
