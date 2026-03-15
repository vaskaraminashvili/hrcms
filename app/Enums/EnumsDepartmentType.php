<?php

namespace App\Enums;

use Filament\Support\Icons\Heroicon;

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

    public function getIcon(): ?Heroicon
    {
        return match ($this) {
            self::DEPARTMENT => Heroicon::ArchiveBox,
            self::POSITION => Heroicon::ArchiveBox,
        };
    }
}
