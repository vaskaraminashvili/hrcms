<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DepartmentType: string implements HasColor, HasLabel
{
    case DEPARTMENT = 'department';
    case INSTITUTION = 'institution';
    case CENTER = 'center';
    case OFFICE = 'office';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEPARTMENT => 'დეპარტამენტი',
            self::INSTITUTION => 'ინსტიტუტი',
            self::CENTER => 'ცენტრი',
            self::OFFICE => 'ოფისი',
            self::OTHER => 'სხვა',
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
            self::INSTITUTION => 'secondary',
            self::CENTER => 'success',
            self::OFFICE => 'warning',
            self::OTHER => 'gray',
        };
    }
}
