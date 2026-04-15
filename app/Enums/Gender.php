<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel
{
    case MALE = 'male';
    case FEMALE = 'female';
    case PREFER_NOT_TO_SAY = 'prefer_not_to_say';

    public function getLabel(): string
    {
        return match ($this) {
            self::MALE => 'მამრობითი',
            self::FEMALE => 'ქალბატონო',
            self::PREFER_NOT_TO_SAY => 'არ ვასახელებ',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }
}
