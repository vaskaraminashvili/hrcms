<?php

namespace App\Filament\Resources\Departments\Fields;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Openplain\FilamentTreeView\Fields\TextField;

class DepartmentDescendantTypeCountField extends TextField
{
    public function render(Model|array $record): string
    {
        if (! $record instanceof Department) {
            return '';
        }

        $url = e(route('departments.type-counts', $record));

        return <<<HTML
            <span
                x-data="{ badges: null }"
                x-init="\$nextTick(() =>
                    fetch('{$url}', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(d => { badges = d })
                )"
                class="flex gap-1 flex-wrap"
            >
                <template x-if="badges === null">
                    <span class="fi-badge rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset fi-color-gray bg-gray-50 text-gray-600 ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20 animate-pulse">···</span>
                </template>
                <template x-if="badges !== null">
                    <span class="flex gap-1 flex-wrap">
                        <template x-for="badge in badges" :key="badge.label">
                            <span
                                class="fi-badge rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                :class="badge.classes"
                                x-text="badge.label + ': ' + badge.count"
                            ></span>
                        </template>
                    </span>
                </template>
            </span>
        HTML;
    }
}
