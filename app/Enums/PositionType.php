<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PositionType: string implements HasLabel
{
    case Emeritus = 'emeritus';
    case AdministrativePersonnel = 'administrative_personnel';
    case AssistantAdministrativePersonnel = 'assistant_administrative_personnel';
    case AcademicPersonnel = 'academic_personnel';
    case InvitedTeacher = 'invited_teacher';
    case ContractedEmployee = 'contracted_employee';
    case AcademicRank = 'academic_rank';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Emeritus => 'ემერიტუსი', // არასაშტატო
            self::AdministrativePersonnel => 'ადმინისტრაციული პერსონალი',
            self::AssistantAdministrativePersonnel => 'დამხმარე ადმინისტრაციული პერსონალი',
            self::AcademicPersonnel => 'აკადემიური პერსონალი',
            self::InvitedTeacher => 'მოწვეული მასწავლებელი', // არასაშტატო
            self::ContractedEmployee => 'ხელშეკრულებით დასაქმებული', // არასაშტატო
            self::AcademicRank => 'აკადემიური წოდება',
        };
    }

    public static function fromLabel(string $search): ?self
    {
        return collect(self::cases())
            ->first(fn ($case) => str_contains($case->getLabel(), $search));
    }

    public function label(): string
    {
        return $this->getLabel() ?? '';
    }

    public function showsClinicalFields(): bool
    {
        return $this === self::AcademicPersonnel;
    }

    public function showsAutomativeRenewal(): bool
    {
        return $this === self::ContractedEmployee;
    }

    public function isNonStaffPositionType(): bool
    {
        return in_array($this, [
            self::Emeritus,
            self::InvitedTeacher,
            self::ContractedEmployee,
        ], true);
    }
}
