<?php

namespace App\Filament\Resources\Departments\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Openplain\FilamentTreeView\Fields\TextField;

class DepartmentTextField extends TextField
{
    /**
     * Render the text field for the given record.
     * Supports HtmlString from formatStateUsing for raw HTML output (e.g. colored text).
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

        return parent::render($record);
    }
}
