<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DepartmentType: string implements HasColor, HasLabel
{
    case DEPARTMENT = 'department';
    case CENTER = 'center';
    case MUSEUM = 'museum';
    case SERVICE = 'service';
    case SECTION = 'section';
    case UNIT = 'unit';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEPARTMENT => 'დეპარტამენტი',
            self::CENTER => 'ცენტრი',
            self::MUSEUM => 'მუზეუმი',
            self::SECTION => 'განყოფილება',
            self::UNIT => 'სამსახური',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DEPARTMENT => 'primary',
            self::CENTER => 'success',
            self::MUSEUM => 'warning',
            self::SERVICE => 'info',
        };
    }
}
