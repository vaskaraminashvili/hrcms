<?php

namespace App\Filament\Resources\Departments\Fields;

/**
 * Renders an active/archived status icon for a tree node.
 * Wraps DepartmentTextField with preset boolean icon/colour pairs.
 */
class DepartmentStatusIconField extends DepartmentTextField
{
    protected bool $boolean = false;

    protected string $trueIcon = 'heroicon-o-check-circle';

    protected string $falseIcon = 'heroicon-o-archive-box';

    protected string $trueColor = 'success';

    protected string $falseColor = 'warning';

    protected bool $alignEnd = true;

    // ── Fluent setters ─────────────────────────────────────

    public function boolean(bool $condition = true): static
    {
        $this->boolean = $condition;

        return $this;
    }

    /**
     * @param  string  $trueIcon  Heroicon name used when the value is truthy
     * @param  string  $falseIcon  Heroicon name used when the value is falsy
     */
    public function icons(string $trueIcon, string $falseIcon): static
    {
        $this->trueIcon = $trueIcon;
        $this->falseIcon = $falseIcon;

        return $this;
    }

    /**
     * @param  string  $trueColor  Tailwind / Filament colour token (success, warning …)
     */
    public function colors(string $trueColor, string $falseColor): static
    {
        $this->trueColor = $trueColor;
        $this->falseColor = $falseColor;

        return $this;
    }

    // ── Getters ────────────────────────────────────────────

    public function isBoolean(): bool
    {
        return $this->boolean;
    }

    public function getIcon(mixed $record): string
    {
        return $this->getState($record) ? $this->trueIcon : $this->falseIcon;
    }

    public function getColor(mixed $record): string
    {
        return $this->getState($record) ? $this->trueColor : $this->falseColor;
    }
}
