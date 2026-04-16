<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PositionStatus: string implements HasColor, HasLabel
{
    case Appointment = 'appointment';       // დანიშვნა
    case Election = 'election';          // არჩევა
    case Dismissal = 'dismissal';         // გათავისუფლება
    case Extension = 'extension';         // ვადის გაგრძელება
    case RankGranted = 'rank_granted';      // წოდების მინიჭება
    case ElectedIndefinite = 'elected_indefinite'; // არჩევა უვადოდ
    case ElectedIndefiniteAttestation = 'elected_indefinite_attestation'; // არჩევა უვადოდ (ეკუთვნის ატესტაცია)
    case Agreement = 'agreement';         // შეთანხმება
    case Achieved = 'achieved';         // დაარქივებული

    public function getLabel(): string
    {
        return match ($this) {
            self::Appointment => 'დანიშვნა',
            self::Election => 'არჩევა',
            self::Dismissal => 'გათავისუფლება',
            self::Extension => 'ვადის გაგრძელება',
            self::RankGranted => 'წოდების მინიჭება',
            self::ElectedIndefinite => 'არჩევა უვადოდ',
            self::ElectedIndefiniteAttestation => 'არჩევა უვადოდ (ეკუთვნის ატესტაცია)',
            self::Agreement => 'შეთანხმება',
            self::Achieved => 'დაარქივებული',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Appointment => 'info',
            self::Election => 'success',
            self::Dismissal => 'danger',
            self::Extension => 'info',
            self::RankGranted => 'primary',
            self::ElectedIndefinite => 'success',
            self::ElectedIndefiniteAttestation => 'warning',
            self::Agreement => 'gray',
            self::Achieved => 'warning',
        };
    }
}
