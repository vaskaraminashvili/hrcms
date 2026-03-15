<?php

namespace App\Filament\Resources\Departments\Fields;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Openplain\FilamentTreeView\Fields\TextField;

class DepartmentTextField extends TextField
{
    protected bool $applyDepartmentColor = false;

    /**
     * Apply department color from the record's color enum.
     * Uses text-{color}-500 Tailwind classes.
     */
    public function withDepartmentColor(bool $apply = true): static
    {
        $this->applyDepartmentColor = $apply;

        return $this;
    }

    /**
     * Render the text field for the given record.
     * Supports HtmlString from formatStateUsing for raw HTML output.
     * When withDepartmentColor() is used, applies color from Department model.
     */
    public function render(Model|array $record): string
    {
        $state = $this->getFieldState($record);

        if ($this->formatStateUsing) {
            $formatted = ($this->formatStateUsing)($state, $record);

            if ($formatted instanceof HtmlString) {
                return (string) $formatted;
            }
        }

        $name = e($record instanceof Department ? ($record->name ?? $state) : $state);
        $color = $this->resolveDepartmentColor($record);

        if ($color !== null) {
            return "<span class='text-sm' style='color: {$color};'>{$name}</span>";
        }

        return parent::render($record);
    }

    protected function resolveDepartmentColor(Model|array $record): ?string
    {
        if (! $this->applyDepartmentColor || ! $record instanceof Department) {
            return null;
        }

        return $record->color?->color() ?? 'gray';
    }
}
