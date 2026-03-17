<?php

namespace App\Enums;

enum PositionType
{
    case Permanent;
    case Temporary;
    case Intern;
    case Contract;
    case Other;

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Permanent',
            self::Temporary => 'Temporary',
            self::Intern => 'Intern',
            self::Contract => 'Contract',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Permanent => 'green',
            self::Temporary => 'yellow',
            self::Intern => 'blue',
            self::Contract => 'yellow',
            self::Other => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Permanent => 'fa-solid fa-user',
            self::Temporary => 'fa-solid fa-user',
            self::Intern => 'fa-solid fa-user',
            self::Contract => 'fa-solid fa-user',
            self::Other => 'fa-solid fa-user',
        };
    }
}
