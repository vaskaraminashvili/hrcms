<?php

namespace App\Enums;

enum PublicHolidayKind: string
{
    case Regular = 'regular';
    case Exceptional = 'exceptional';
    case YearlyPlanned = 'yearly_planned';

    public function label(): string
    {
        return match ($this) {
            self::Regular => __('filament.public_holiday_kind.regular'),
            self::Exceptional => __('filament.public_holiday_kind.exceptional'),
            self::YearlyPlanned => __('filament.public_holiday_kind.yearly_planned'),
        };
    }
}
