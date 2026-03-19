<?php

namespace App\Enums;

enum DepartmentStatus: string
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
    case INACTIVE = 'inactive'; // keep whatever

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ARCHIVED => 'Archived',
            self::INACTIVE => 'Inactive',
        };
    }
}
