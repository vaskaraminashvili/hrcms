<?php

namespace App\Filament\Resources\Departments\Fields;

use Closure;
use Filament\Support\Components\Component;
use Illuminate\Support\Str;

/**
 * A lightweight inline text field for the tree view.
 * Supports limiting string length, linking, and badge rendering.
 */
class DepartmentTextField extends Component
{
    protected string $column;

    // ── Display options ────────────────────────────────────
    protected ?int $limit = null;

    protected string $limitSuffix = '...';

    protected bool $alignEnd = false;

    // ── Link options ───────────────────────────────────────
    protected Closure|string|null $url = null;

    protected bool $openUrlInNewTab = false;

    // ── Badge options ──────────────────────────────────────
    protected bool $badge = false;

    protected string $badgeColor = 'gray';

    // ── Visibility ─────────────────────────────────────────
    protected Closure|bool $hidden = false;

    // ── State formatter ────────────────────────────────────
    protected ?Closure $formatStateUsing = null;

    // ── Factory ────────────────────────────────────────────

    final public function __construct(string $column)
    {
        $this->column = $column;
    }

    public static function make(string $column): static
    {
        $instance = app(static::class, ['column' => $column]);
        $instance->setUp();

        return $instance;
    }

    protected function setUp(): void {}

    // ── Fluent setters ─────────────────────────────────────

    public function limit(int $length, string $suffix = '...'): static
    {
        $this->limit = $length;
        $this->limitSuffix = $suffix;

        return $this;
    }

    public function url(Closure|string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function openUrlInNewTab(bool $condition = true): static
    {
        $this->openUrlInNewTab = $condition;

        return $this;
    }

    public function alignEnd(bool $condition = true): static
    {
        $this->alignEnd = $condition;

        return $this;
    }

    public function badge(bool $condition = true): static
    {
        $this->badge = $condition;

        return $this;
    }

    public function badgeColor(string $color): static
    {
        $this->badgeColor = $color;

        return $this;
    }

    public function hidden(Closure|bool $condition = true): static
    {
        $this->hidden = $condition;

        return $this;
    }

    public function formatStateUsing(Closure $callback): static
    {
        $this->formatStateUsing = $callback;

        return $this;
    }

    // ── Getters ────────────────────────────────────────────

    public function getColumn(): string
    {
        return $this->column;
    }

    public function isAlignEnd(): bool
    {
        return $this->alignEnd;
    }

    public function isBadge(): bool
    {
        return $this->badge;
    }

    public function getBadgeColor(): string
    {
        return $this->badgeColor;
    }

    public function isHidden(mixed $record): bool
    {
        $hidden = $this->hidden;

        return $hidden instanceof Closure ? $hidden($record) : (bool) $hidden;
    }

    public function getState(mixed $record): mixed
    {
        $state = data_get($record, $this->column);

        if ($this->formatStateUsing) {
            $state = ($this->formatStateUsing)($state);
        }

        return $state;
    }

    public function getDisplayValue(mixed $record): string
    {
        $value = (string) $this->getState($record);

        if ($this->limit !== null) {
            $value = Str::limit($value, $this->limit, $this->limitSuffix);
        }

        return $value;
    }

    public function getUrl(mixed $record): ?string
    {
        if ($this->url === null) {
            return null;
        }

        $url = $this->url;

        return $url instanceof Closure ? $url($record) : $url;
    }

    public function isOpenUrlInNewTab(): bool
    {
        return $this->openUrlInNewTab;
    }
}
