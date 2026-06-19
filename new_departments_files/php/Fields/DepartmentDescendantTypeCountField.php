<?php

namespace App\Filament\Resources\Departments\Fields;

use Closure;

/**
 * Renders the coloured descendant-type count badges for a tree node.
 * The payload (array of [label, count, color]) is resolved via a closure
 * so it can call the DepartmentDescendantTypeCountService per record.
 */
class DepartmentDescendantTypeCountField extends DepartmentTextField
{
    protected ?Closure $payloadUsing = null;

    protected bool $alignEnd = true;

    // ── Fluent setter ──────────────────────────────────────

    public function payloadUsing(Closure $callback): static
    {
        $this->payloadUsing = $callback;

        return $this;
    }

    // ── Getter ─────────────────────────────────────────────

    /**
     * Returns an array of badge descriptors for the given record.
     *
     * Each descriptor is an associative array with keys:
     *   - label (string)  – human-readable type name
     *   - count (int)     – total descendants of that type
     *   - color (string)  – badge colour key (matches $badge-palettes in tokens.scss)
     *
     * @return array<int, array{label: string, count: int, color: string}>
     */
    public function getPayload(mixed $record): array
    {
        if ($this->payloadUsing === null) {
            return [];
        }

        $payload = ($this->payloadUsing)($record);

        // Filter out zero-count entries so the row stays clean
        return array_filter($payload, fn (array $item): bool => ($item['count'] ?? 0) > 0);
    }
}
