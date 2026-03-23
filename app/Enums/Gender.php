<?php

namespace App\Enums;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case PREFER_NOT_TO_SAY = 'prefer_not_to_say';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'მამრობითი',
            self::FEMALE => 'ქალბატონო',
            self::PREFER_NOT_TO_SAY => 'Prefer not to say',
        };
    }
}
