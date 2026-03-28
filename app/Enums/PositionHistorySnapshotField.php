<?php

namespace App\Enums;

use App\Models\Place;
use Carbon\Carbon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Number;
use UnitEnum;

/**
 * Maps snapshot / diff keys to display formatting for history views.
 * Labels use {@see self::labelForSnapshotKey()} and `filament.changed_fields.{key}` when present.
 * Diff and snapshot cell text use {@see self::formatDiffSegment()} and {@see self::formatDiffValue()}.
 */
enum PositionHistorySnapshotField: string
{
    /**
     * Not stored in history snapshots / diffs (observer) and hidden when displaying stored rows.
     *
     * @var list<string>
     */
    public const EXCLUDED_FROM_HISTORY = ['employee_id', 'department_id', 'vacation_policy_id', 'updated_at'];

    case Comment = 'comment';
    case PlaceId = 'place_id';
    case Salary = 'salary';
    case Status = 'status';
    case PositionType = 'position_type';
    case DateStart = 'date_start';
    case DateEnd = 'date_end';
    case ActDate = 'act_date';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';
    case Clinical = 'clinical';
    case StaffType = 'staff_type';

    /**
     * Human-readable label for a snapshot array key (infolist full snapshot, etc.).
     * Known keys use translations; others fall back to title-cased snake_case.
     */
    public static function labelForSnapshotKey(string $key): string
    {
        $translationKey = 'filament.changed_fields.'.$key;

        if (Lang::has($translationKey)) {
            return __($translationKey);
        }

        return str($key)->replace('_', ' ')->title()->toString();
    }

    /**
     * @param  array<string, mixed>|mixed  $diff
     */
    public static function formatDiffSegment(mixed $diff, ?string $key = null): string
    {
        if (! is_array($diff)) {
            return self::formatDiffValue($diff, $key);
        }

        if (! array_key_exists('from', $diff) && ! array_key_exists('to', $diff)) {
            return json_encode($diff, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return self::formatDiffValue($diff['from'] ?? null, $key).' → '.self::formatDiffValue($diff['to'] ?? null, $key);
    }

    public static function formatDiffValue(mixed $value, ?string $key = null): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        if ($value instanceof UnitEnum) {
            if ($value instanceof HasLabel) {
                return (string) ($value->getLabel() ?? ($value instanceof \BackedEnum ? $value->value : $value->name));
            }

            return $value instanceof \BackedEnum ? (string) $value->value : $value->name;
        }

        if ($key !== null && ($field = self::tryFrom($key))) {
            return $field->formatValue($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    public function getLabel(): ?string
    {
        return self::labelForSnapshotKey($this->value);
    }

    public function formatValue(mixed $value): string
    {
        if ($value instanceof UnitEnum) {
            if ($value instanceof HasLabel) {
                return (string) ($value->getLabel() ?? ($value instanceof \BackedEnum ? $value->value : $value->name));
            }

            return $value instanceof \BackedEnum ? (string) $value->value : $value->name;
        }

        return match ($this) {
            self::Comment => strip_tags((string) $value),
            self::PlaceId => Place::find($value)?->name ?? (string) $value,
            self::Salary => Number::currency(intval($value), 'GEL', 'ka', 0),
            self::Status => PositionStatus::from($value)->getLabel(),
            self::PositionType => PositionType::from($value)->getLabel() ?? (string) $value,
            self::DateStart,
            self::DateEnd,
            self::ActDate,
            self::CreatedAt,
            self::UpdatedAt => Carbon::parse($value)->format('d-m-Y'),
            self::Clinical => (bool) $value
                ? __('filament.clinical_option.clinical')
                : __('filament.clinical_option.non_clinical'),
            self::StaffType => match ((string) $value) {
                '1' => __('filament.staff_type_option.established'),
                '2' => __('filament.staff_type_option.non_established'),
                default => (string) $value,
            },
        };
    }
}
