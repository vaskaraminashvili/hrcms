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
            self::ACTIVE => 'აქტიური',
            self::ARCHIVED => 'დაარქივებული',
            self::INACTIVE => 'გათიშული',
        };
    }
}
