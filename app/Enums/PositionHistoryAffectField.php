<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Single source of truth for position history "affects_*" columns and how they map to
 * {@see Position} dirty attributes. Filament table columns, filters, and infolist icons
 * are built from this enum; adjust visibility methods per case when a field should appear
 * in filters but not in the table (or the opposite).
 */
enum PositionHistoryAffectField: string implements HasLabel
{
    case Salary = 'affects_salary';
    case Status = 'affects_status';
    case PositionType = 'affects_position_type';
    case StaffType = 'affects_staff_type';
    case DateStart = 'affects_date_start';
    case DateEnd = 'affects_date_end';
    case Clinical = 'affects_clinical';
    case Place = 'affects_place';
    case ActNumber = 'affects_act_number';

    public function getLabel(): ?string
    {
        return __('filament.position_history_affects.'.$this->name);
    }

    /**
     * @return list<string> Position model attribute names that set this flag when dirty.
     */
    public function positionDirtyKeys(): array
    {
        return match ($this) {
            self::Salary => ['salary'],
            self::Status => ['status'],
            self::PositionType => ['position_type'],
            self::StaffType => ['staff_type'],
            self::DateStart => ['date_start'],
            self::DateEnd => ['date_end'],
            self::Clinical => ['clinical'],
            self::Place => ['place_id'],
            self::ActNumber => ['act_number'],
        };
    }

    public function shouldMarkAffectedOnCreate(): bool
    {
        return true;
    }

    /**
     * Table icon columns. Return false for a case to hide it here while keeping filters/infolist enabled.
     */
    public function showInTableColumn(): bool
    {
        return match ($this) {
            self::Salary => false,
            self::Status => false,
            self::PositionType => false,
            self::StaffType => false,
            self::DateStart => false,
            self::DateEnd => false,
            self::Clinical => false,
            self::Place => false,
            self::ActNumber => false,
            default => true,
        };
    }

    /**
     * Ternary filters on the list table. Return false to omit a filter for that flag.
     */
    public function showInFilter(): bool
    {
        return match ($this) {
            default => true,
        };
    }

    /**
     * “Affects” icon section on the view infolist. Return false to hide that flag on the detail page only.
     */
    public function showInInfolist(): bool
    {
        return match ($this) {
            default => true,
        };
    }

    public function isAffectedByDirty(?array $dirty): bool
    {
        $dirty ??= [];

        foreach ($this->positionDirtyKeys() as $key) {
            if (array_key_exists($key, $dirty)) {
                return true;
            }
        }

        return false;
    }
}
