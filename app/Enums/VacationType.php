<?php

namespace App\Enums;

enum VacationType: string
{
    case CurrentYear = 'current_year';
    case PreviousYear = 'previous_year';

    public function label(): string
    {
        return match ($this) {
            self::CurrentYear => 'მიმდინარე წელი',
            self::PreviousYear => 'წინა წელი',
        };
    }
}
