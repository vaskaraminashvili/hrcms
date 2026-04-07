<?php

namespace App\Jobs;

use App\Models\Employee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Throwable;

class ImportEmployeeImageChunkJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, array{id:int,photo:?string,name:?string,surname:?string}>  $rows
     */
    public function __construct(private readonly array $rows) {}

    /**
     * Execute the job.
     *
     * @return array{imported:int,skipped:int,not_found:int,failed:int}
     */
    public function handle(): array
    {
        $imported = 0;
        $skipped = 0;
        $notFound = 0;
        $failed = 0;

        foreach ($this->rows as $row) {
            $importEmployeeId = (int) ($row['id'] ?? 0);
            $photo = trim((string) ($row['photo'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $surname = trim((string) ($row['surname'] ?? ''));

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

                Log::warning('Image import employee not found.', [
                    'import_employee_id' => $importEmployeeId,
                    'name' => $name,
                    'surname' => $surname,
                ]);

                continue;
            }

            $photoUrl = $this->normalizePhotoUrl($photo);
            $filename = $this->extractOriginalFilename($photoUrl);

            try {
                $employee->clearMediaCollection('employee_image');
                $employee->addMediaFromUrl($photoUrl)
                    ->usingName(pathinfo($filename, PATHINFO_FILENAME))
                    ->usingFileName($filename)
                    ->toMediaCollection('employee_image');

                $imported++;
            } catch (FileCannotBeAdded|Throwable $exception) {
                $failed++;

                Log::error('Image import failed for employee.', [
                    'employee_id' => $employee->id,
                    'import_employee_id' => $importEmployeeId,
                    'photo_url' => $photoUrl,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

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
            return $this->encodeUrlPath($photo);
        }

        return $this->encodeUrlPath('https://sms.tsmu.edu/hr/img/'.ltrim($photo, '/'));
    }

    private function extractOriginalFilename(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $basename = basename($path);
        $decoded = urldecode($basename);

        return $decoded !== '' ? $decoded : 'employee-image.jpg';
    }

    private function encodeUrlPath(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return str_replace(' ', '%20', $url);
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'].'://' : '';
        $user = $parts['user'] ?? '';
        $pass = isset($parts['pass']) ? ':'.$parts['pass'] : '';
        $auth = $user !== '' ? $user.$pass.'@' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '';

        if ($path !== '') {
            $path = implode('/', array_map(
                static fn (string $segment): string => rawurlencode(urldecode($segment)),
                explode('/', $path)
            ));
        }

        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return $scheme.$auth.$host.$port.$path.$query.$fragment;
    }
}
