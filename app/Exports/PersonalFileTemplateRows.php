<?php

namespace App\Exports;

use App\Models\Education;
use App\Models\Publication;
use App\Models\ScholarshipAward;
use App\Models\ScientificForum;
use App\Models\Textbook;
use App\Models\TrainingSeminar;
use App\Models\WorkExperience;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class PersonalFileTemplateRows
{
    /**
     * @return array<string, mixed>
     */
    public static function education(Education $record): array
    {
        return [
            ...self::translatableColumns($record, 'institution', 'institution'),
            ...self::translatableColumns($record, 'program', 'program'),
            ...self::translatableColumns($record, 'specialty', 'specialty'),
            'started_at' => self::date($record->started_at),
            'ended_at' => self::date($record->ended_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function publication(Publication $record): array
    {
        $details = is_array($record->publication_details)
            ? $record->publication_details
            : Publication::defaultPublicationDetails();

        return [
            'year' => $record->published_at,
            ...self::translatableColumns($record, 'title', 'title'),
            ...self::translatableColumns($record, 'co_authors', 'authors'),
            ...self::translatableColumns($record, 'place', 'venue'),
            'scope' => self::plain($details['scope'] ?? null),
            'indexed' => self::booleanCell((bool) ($details['indexed'] ?? false)),
            'impact_factor' => self::booleanCell((bool) ($details['impact_factor'] ?? false)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function scholarshipAward(ScholarshipAward $record): array
    {
        return [
            ...self::translatableColumns($record, 'title', 'title'),
            ...self::translatableColumns($record, 'issuer', 'issuer'),
            ...self::translatableColumns($record, 'grant_details', 'grant_details'),
            'issued_at' => self::plain($record->issued_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function scientificForum(ScientificForum $record): array
    {
        return [
            ...self::translatableColumns($record, 'title', 'title'),
            'international_forum_publication' => self::booleanCell((bool) $record->international_forum_publication),
            ...self::translatableColumns($record, 'participation_form', 'participation_form'),
            'start_date' => self::date($record->getAttribute('start_date')),
            'end_date' => self::date($record->getAttribute('end_date')),
            'participation_role' => self::plain($record->participation_role),
            'scope' => self::plain($record->scope),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function textbook(Textbook $record): array
    {
        return [
            ...self::translatableColumns($record, 'title', 'title'),
            ...self::translatableColumns($record, 'publisher', 'publisher'),
            ...self::translatableColumns($record, 'co_authors', 'co_authors'),
            'published_at' => self::plain($record->published_at),
            'page_count' => $record->page_count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function trainingSeminar(TrainingSeminar $record): array
    {
        return [
            ...self::translatableColumns($record, 'institution', 'institution'),
            ...self::translatableColumns($record, 'topic', 'topic'),
            'started_at' => self::date($record->started_at),
            'ended_at' => self::date($record->ended_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function workExperience(WorkExperience $record): array
    {
        return [
            ...self::translatableColumns($record, 'institution', 'institution'),
            ...self::translatableColumns($record, 'position', 'position'),
            'started_at' => self::date($record->started_at),
            'ended_at' => self::date($record->ended_at),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function translatableColumns(Model $record, string $attribute, string $column): array
    {
        return [
            "{$column}_ka" => self::text($record, $attribute, 'ka'),
            "{$column}_en" => self::text($record, $attribute, 'en'),
        ];
    }

    private static function text(Model $record, string $attribute, string $locale): string
    {
        if (! method_exists($record, 'getTranslation')) {
            return '';
        }

        $value = $record->getTranslation($attribute, $locale, false);

        return is_string($value) ? $value : '';
    }

    private static function date(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return trim($value);
        }
    }

    private static function plain(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private static function booleanCell(bool $value): string
    {
        return $value ? '1' : '0';
    }
}
