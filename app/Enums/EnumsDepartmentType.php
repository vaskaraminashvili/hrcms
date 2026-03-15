<?php

namespace App\Enums;

enum EnumsDepartmentType: string
{
    case DEPARTMENT = 'department';
    case POSITION = 'position';

    public function label(): string
    {
        return match ($this) {
            self::DEPARTMENT => 'Department',
            self::POSITION => 'Position',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DEPARTMENT => 'gray',
            self::POSITION => 'blue',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DEPARTMENT => 'heroicon-o-hourglass',
            self::POSITION => 'heroicon-o-check-circle',
        };
    }
}
