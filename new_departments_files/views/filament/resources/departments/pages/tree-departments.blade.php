<x-filament-panels::page>
    <div
        class="dept-tree-page"
        x-data="departmentTree()"
        x-init="init()"
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
            @foreach ($records->whereNull('parent_id') as $record)
                <x-filament.resources.departments.components.tree-node
                    :record="$record"
                    :depth="0"
                    :all-records="$records"
                />
            @endforeach
        </div>
    </div>

    @push('scripts')
        <script>
            function departmentTree() {
                return {
                    init() {},

                    expandAll() {
                        this.$el.querySelectorAll('[data-tree-children]').forEach(el => {
                            el.style.display = 'block';
                        });
                        this.$el.querySelectorAll('[data-tree-chevron]').forEach(el => {
                            el.classList.remove('dept-tree-node__chevron--collapsed');
                        });
                        this.$el.querySelectorAll('[data-tree-toggle]').forEach(el => {
                            el.setAttribute('aria-expanded', 'true');
                        });
                    },

                    collapseAll() {
                        this.$el.querySelectorAll('[data-tree-children]').forEach(el => {
                            el.style.display = 'none';
                        });
                        this.$el.querySelectorAll('[data-tree-chevron]').forEach(el => {
                            el.classList.add('dept-tree-node__chevron--collapsed');
                        });
                        this.$el.querySelectorAll('[data-tree-toggle]').forEach(el => {
                            el.setAttribute('aria-expanded', 'false');
                        });
                    },
                }
            }

            function treeNode(hasChildren) {
                return {
                    open: true,

                    toggle() {
                        if (!hasChildren) return;
                        this.open = !this.open;
                    },
                }
            }
        </script>
    @endpush
</x-filament-panels::page>
