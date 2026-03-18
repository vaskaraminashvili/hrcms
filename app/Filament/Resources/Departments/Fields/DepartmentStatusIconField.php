<?php

namespace App\Filament\Resources\Departments\Fields;

use App\Enums\DepartmentStatus;
use Filament\Support\Enums\IconSize;
use Illuminate\Database\Eloquent\Model;
use Openplain\FilamentTreeView\Fields\IconField;

use function Filament\Support\generate_icon_html;

class DepartmentStatusIconField extends IconField
{
    public function render(Model|array $record): string
    {
        $status = data_get($record, $this->name);
        $isActive = $status === DepartmentStatus::ACTIVE;

        $icon = $isActive ? $this->trueIcon : $this->falseIcon;
        $color = $isActive ? $this->trueColor : $this->falseColor;

        $iconHtml = generate_icon_html($icon, size: IconSize::Medium);

        return sprintf(
            '<div class="fi-tree-toggle-icon fi-color-%s">%s</div>',
            $color,
            $iconHtml->toHtml()
        );
    }
}
