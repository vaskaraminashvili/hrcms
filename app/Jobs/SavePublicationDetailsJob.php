<?php

namespace App\Jobs;

use App\Models\Publication;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SavePublicationDetailsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    /**
     * @param  array{scope: string, indexed: bool, impact_factor: bool}  $details
     */
    public function __construct(
        public int $employeeId,
        public int $publicationId,
        public array $details,
        public string $statusKey,
    ) {}

    public function handle(): void
    {
        $updated = Publication::applyPublicationDetails(
            $this->employeeId,
            [$this->publicationId],
            $this->details,
        );

        Cache::put(
            $this->statusKey,
            $updated === 1 ? 'saved' : 'missing',
            now()->addMinutes(5),
        );
    }

    public function failed(?Throwable $exception): void
    {
        Cache::put($this->statusKey, 'failed', now()->addMinutes(5));

        Log::error('Publication classification save failed.', [
            'employee_id' => $this->employeeId,
            'publication_id' => $this->publicationId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
