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
    case EDITORIAL = 'editorial';
    case APPARATUS = 'apparatus';
    case COUNCIL = 'council';
    case CLINIC = 'clinic';
    case FACULTY = 'faculty';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEPARTMENT => 'დეპარტამენტი',
            self::CENTER => 'ცენტრი',
            self::MUSEUM => 'მუზეუმი',
            self::SERVICE => 'სამსახური',
            self::SECTION => 'განყოფილება',
            self::EDITORIAL => 'რედაქცია',
            self::APPARATUS => 'აპარატი',
            self::COUNCIL => 'საბჭო',
            self::CLINIC => 'კლინიკა',
            self::FACULTY => 'ფაკულტეტი',
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
            self::SECTION => 'danger',
            self::EDITORIAL => 'primary',
            self::APPARATUS => 'success',
            self::COUNCIL => 'warning',
            self::CLINIC => 'danger',
            self::FACULTY => 'primary',
        };
    }
}
