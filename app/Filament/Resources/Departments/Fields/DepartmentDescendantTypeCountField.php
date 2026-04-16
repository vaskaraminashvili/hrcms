<?php

namespace App\Filament\Resources\Departments\Fields;

use App\Models\Department;
use App\Services\DepartmentDescendantTypeCountService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Openplain\FilamentTreeView\Fields\TextField;

class DepartmentDescendantTypeCountField extends TextField
{
    /** @var (Closure(Department): array<int, array{label: string, count: int, classes: string}>)|null */
    protected ?Closure $payloadUsing = null;

    /**
     * Resolve badge rows for the given department (defaults to cached service payload).
     *
     * @param  Closure(Department): array<int, array{label: string, count: int, classes: string}>  $callback
     */
    public function payloadUsing(Closure $callback): static
    {
        $this->payloadUsing = $callback;

        return $this;
    }

    public function render(Model|array $record): string
    {
        if (! $record instanceof Department) {
            return '';
        }

        $badges = $this->payloadUsing !== null
            ? ($this->payloadUsing)($record)
            : app(DepartmentDescendantTypeCountService::class)->getCachedDescendantTypeCountsPayload($record);

        $html = '';
        foreach ($badges as $badge) {
            $html .= '<span class="fi-badge rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset '.e($badge['classes']).'">'
                .e($badge['label']).': '.e((string) $badge['count'])
                .'</span>';
        }

        return '<span class="flex gap-1 flex-wrap">'.$html.'</span>';
    }
}
