<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EmployeeStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case ACTIVE = 'ACTIVE';
    case ARCHIVED = 'ARCHIVED';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => __('filament.active'),
            self::ARCHIVED => __('filament.archived'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::ARCHIVED => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ACTIVE => 'heroicon-o-check-circle',
            self::ARCHIVED => 'heroicon-o-archive-box',
        };
    }
}
