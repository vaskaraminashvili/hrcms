<?php

namespace App\Support;

class DepartmentBadgeColors
{
    /** @var list<string> */
    private const TREE_PALETTE_KEYS = [
        'gray', 'purple', 'teal', 'coral', 'pink', 'amber', 'blue', 'green',
        'info', 'success', 'warning', 'danger',
    ];

    /** @var array<string, string> */
    public const BADGE_COLOR_CLASSES = [
        'gray' => 'fi-color fi-color-gray fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'primary' => 'fi-color fi-color-primary fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'success' => 'fi-color fi-color-success fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'warning' => 'fi-color fi-color-warning fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'danger' => 'fi-color fi-color-danger fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'info' => 'fi-color fi-color-info fi-text-color-700 dark:fi-text-color-300 fi-badge',
    ];

    public static function treePaletteKey(string $color): string
    {
        return match ($color) {
            'primary' => 'purple',
            'info' => 'blue',
            'success' => 'green',
            'warning' => 'amber',
            'danger' => 'coral',
            default => in_array($color, self::TREE_PALETTE_KEYS, true) ? $color : 'gray',
        };
    }
}
