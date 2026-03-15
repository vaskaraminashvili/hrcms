<?php

namespace App\Filament\Resources\Departments\Fields;

use App\Models\Department;
use Filament\Support\Enums\IconSize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Openplain\FilamentTreeView\Fields\TextField;

use function Filament\Support\generate_icon_html;

class DepartmentTextField extends TextField
{
    protected bool $applyDepartmentColor = false;

    protected bool $applyDepartmentIcon = false;

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
     * Apply department icon from the record's type enum (EnumsDepartmentType::getIcon).
     */
    public function withDepartmentIcon(bool $apply = true): static
    {
        $this->applyDepartmentIcon = $apply;

        return $this;
    }

    /**
     * Render the text field for the given record.
     * Supports HtmlString from formatStateUsing for raw HTML output.
     * When withDepartmentColor() is used, applies color from Department model.
     * When withDepartmentIcon() is used, prepends icon from EnumsDepartmentType::getIcon.
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
        $iconHtml = $this->resolveDepartmentIcon($record);
        $color = $this->resolveDepartmentColor($record);

        if ($iconHtml !== null) {
            $textSpan = $color !== null
                ? "<span style=\"color: {$color};\">{$name}</span>"
                : $name;

            return '<span class="flex items-center text-sm">'
                .$iconHtml
                .$textSpan
                .'</span>';
        }

        if ($color !== null) {
            return "<span class='text-sm' style='color: {$color};'>{$name}</span>";
        }

        return parent::render($record);
    }

    protected function resolveDepartmentIcon(Model|array $record): ?string
    {
        if (! $this->applyDepartmentIcon || ! $record instanceof Department) {
            return null;
        }

        $icon = $record->type?->getIcon();

        if ($icon === null) {
            return null;
        }

        $html = generate_icon_html($icon, size: IconSize::Small);

        return '<span class="inline-flex" style="margin-right: 8px;">'.$html?->toHtml().'</span>';
    }

    protected function resolveDepartmentColor(Model|array $record): ?string
    {
        if (! $this->applyDepartmentColor || ! $record instanceof Department) {
            return null;
        }

        return $record->color?->color() ?? 'gray';
    }
}
