<?php

namespace App\Enums;

use Filament\Support\Icons\Heroicon;

enum StatusEnum: string
{
    case ACTIVE = 'ACTIVE';
    case SUCCESS = 'SUCCESS';
    case WARNING = 'WARNING';
    case DANGER = 'DANGER';
    case INFO = 'INFO';
    case GRAY = 'GRAY';

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'active',
            self::SUCCESS => 'success',
            self::WARNING => 'warning',
            self::DANGER => 'danger',
            self::INFO => 'info',
            self::GRAY => 'gray',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUCCESS => 'Success',
            self::WARNING => 'Warning',
            self::DANGER => 'Danger',
            self::INFO => 'Info',
            self::GRAY => 'Gray',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::ACTIVE => Heroicon::OutlinedCheckCircle,
            self::SUCCESS => Heroicon::OutlinedCheckCircle,
            self::WARNING => Heroicon::OutlinedExclamationTriangle,
            self::DANGER => Heroicon::OutlinedExclamationTriangle,
            self::INFO => Heroicon::OutlinedInformationCircle,
            self::GRAY => Heroicon::OutlinedClock,
        };
    }
}
