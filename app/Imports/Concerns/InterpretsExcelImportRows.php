<?php

namespace App\Imports\Concerns;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

trait InterpretsExcelImportRows
{
    /**
     * @return array{ka: string, en: string}
     */
    protected function translatable(string $value): array
    {
        return ['ka' => $value, 'en' => $value];
    }

    protected function string(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    protected function optionalTranslatable(mixed $value): ?array
    {
        $s = $this->string($value);

        return $s !== '' ? $this->translatable($s) : null;
    }

    /**
     * @return array{ka: string, en: string}
     */
    protected function translatableFromKaEn(string $ka, string $en): array
    {
        return [
            'ka' => $ka !== '' ? $ka : $en,
            'en' => $en !== '' ? $en : $ka,
        ];
    }

    /**
     * @return array{ka: string, en: string}|null
     */
    protected function optionalTranslatableFromRow(array $row, string $base): ?array
    {
        $ka = $this->string($row["{$base}_ka"] ?? null);
        $en = $this->string($row["{$base}_en"] ?? null);

        if ($ka === '' && $en === '') {
            return null;
        }

        return $this->translatableFromKaEn($ka, $en);
    }

    /**
     * @return array{ka: string, en: string}|null
     */
    protected function requiredTranslatableFromRow(array $row, string $base): ?array
    {
        return $this->optionalTranslatableFromRow($row, $base);
    }

    protected function optionalDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface || $value instanceof \DateTimeInterface) {
            return Carbon::parse($value)->format('Y-m-d');
        }

        if (is_numeric($value)) {
            $num = (float) $value;
            if ($num == (int) $num && $num >= 1900 && $num <= 2100) {
                return Carbon::createFromDate((int) $num, 1, 1)->format('Y-m-d');
            }

            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($num))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            try {
                return Carbon::parse($trimmed)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
