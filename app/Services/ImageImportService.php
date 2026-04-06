<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Throwable;

class ImageImportService
{
    private const IMAGE_COLLECTION = 'employee_image';

    private const LEGACY_IMAGE_BASE_URL = 'https://sms.tsmu.edu/hr/img/';

    private const IMPORT_CHUNK_SIZE = 200;

    /**
     * @return array{imported: int, skipped: int, not_found: int, failed: int}
     */
    public function importAll(bool $clearTableBefore = true): array
    {
        set_time_limit(0);

        $imported = 0;
        $skipped = 0;
        $notFound = 0;
        $failed = 0;

        // if ($clearTableBefore) {
        //     Employee::query()
        //         ->chunkById(self::IMPORT_CHUNK_SIZE, function ($employees): void {
        //             foreach ($employees as $employee) {
        //                 $employee->clearMediaCollection(self::IMAGE_COLLECTION);
        //             }
        //         });
        // }

        DB::table('import_employees')
            ->select('id', 'photo', 'name', 'surname')
            ->whereNotNull('photo')
            ->orderBy('id')
            ->chunkById(self::IMPORT_CHUNK_SIZE, function ($rows) use (&$imported, &$skipped, &$notFound, &$failed): void {
                foreach ($rows as $row) {
                    $photo = trim((string) ($row->photo ?? ''));
                    $name = trim((string) ($row->name ?? ''));
                    $surname = trim((string) ($row->surname ?? ''));

                    if ($photo === '' || $name === '' || $surname === '') {
                        $skipped++;

                        continue;
                    }

                    $employee = Employee::query()
                        ->where('name', $name)
                        ->where('surname', $surname)
                        ->first();

                    if (! $employee instanceof Employee) {
                        $notFound++;

                        continue;
                    }

                    $photoUrl = $this->normalizePhotoUrl($photo);
                    $filename = $this->extractOriginalFilename($photoUrl);

                    try {
                        $employee->clearMediaCollection(self::IMAGE_COLLECTION);
                        $employee->addMediaFromUrl($photoUrl)
                            ->usingName(pathinfo($filename, PATHINFO_FILENAME))
                            ->usingFileName($filename)
                            ->toMediaCollection(self::IMAGE_COLLECTION);
                        $imported++;
                        dd('asd');
                    } catch (FileCannotBeAdded|Throwable) {
                        $failed++;
                    }
                }
            });

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'not_found' => $notFound,
            'failed' => $failed,
        ];
    }

    private function normalizePhotoUrl(string $photo): string
    {
        if (Str::startsWith($photo, ['http://', 'https://'])) {
            return $photo;
        }

        return rtrim(self::LEGACY_IMAGE_BASE_URL, '/').'/'.ltrim($photo, '/');
    }

    private function extractOriginalFilename(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $basename = basename($path);
        $decoded = urldecode($basename);

        return $decoded !== '' ? $decoded : 'employee-image.jpg';
    }
}
