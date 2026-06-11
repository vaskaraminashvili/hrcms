<x-filament-panels::page>
    <div
        class="dept-tree-page"
        x-data="{
            expandAll() {
                window.dispatchEvent(new CustomEvent('dept-tree-set-open', { detail: true }));
            },
            collapseAll() {
                window.dispatchEvent(new CustomEvent('dept-tree-set-open', { detail: false }));
            },
        }"
    >
        {{-- Page Header Toolbar --}}
        <div class="dept-tree-toolbar">
            <div class="dept-tree-toolbar__actions">
                <button
                    type="button"
                    class="dept-tree-toolbar__btn"
                    x-on:click="expandAll()"
                >
                    <x-heroicon-o-plus class="dept-tree-toolbar__btn-icon" />
                    {{ __('filament.resources.departments.expand_all') }}
                </button>
                <button
                    type="button"
                    class="dept-tree-toolbar__btn"
                    x-on:click="collapseAll()"
                >
                    <x-heroicon-o-minus class="dept-tree-toolbar__btn-icon" />
                    {{ __('filament.resources.departments.collapse_all') }}
                </button>
            </div>
        </div>

        {{-- Tree Container --}}
        <div class="dept-tree" role="tree" aria-label="{{ __('filament.resources.departments.plural_model_label') }}">
            @foreach ($this->getRecords()->whereNull('parent_id') as $record)
                <x-filament.resources.departments.components.tree-node
                    :record="$record"
                    :depth="0"
                    :all-records="$this->getRecords()"
                />
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
