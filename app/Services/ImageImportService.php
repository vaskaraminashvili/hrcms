<?php

namespace App\Services;

use App\Jobs\ImportEmployeeImageChunkJob;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class ImageImportService
{
    private const IMPORT_CHUNK_SIZE = 50;

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

        if ($clearTableBefore) {
            Employee::query()
                ->chunkById(self::IMPORT_CHUNK_SIZE, function ($employees): void {
                    foreach ($employees as $employee) {
                        $employee->clearMediaCollection('employee_image');
                    }
                });
        }

        DB::table('import_employees')
            ->select('id', 'photo', 'name', 'surname')
            ->whereNotNull('photo')
            ->limit(60)
            ->orderBy('id')
            ->chunkById(self::IMPORT_CHUNK_SIZE, function ($rows) use (&$imported, &$skipped, &$notFound, &$failed): void {
                /** @var array<int, array{id:int,photo:?string,name:?string,surname:?string}> $chunkRows */
                $chunkRows = $rows
                    ->map(fn ($row): array => [
                        'id' => (int) ($row->id ?? 0),
                        'photo' => $row->photo,
                        'name' => $row->name,
                        'surname' => $row->surname,
                    ])
                    ->all();

                /** @var array{imported:int,skipped:int,not_found:int,failed:int} $chunkResult */
                $chunkResult = (new ImportEmployeeImageChunkJob($chunkRows))->handle();

                $imported += $chunkResult['imported'];
                $skipped += $chunkResult['skipped'];
                $notFound += $chunkResult['not_found'];
                $failed += $chunkResult['failed'];
            });

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'not_found' => $notFound,
            'failed' => $failed,
        ];
    }
}
