<?php

namespace App\Enums;

enum PositionStatus
{
    case Active;
    case Inactive;
    case Pending;
    case Archived;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Pending => 'Pending',
            self::Archived => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'warning',
            self::Pending => 'info',
            self::Archived => 'danger',
        };
    }
}
