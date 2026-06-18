@php
    $children    = $allRecords->where('parent_id', $record->getKey());
    $hasChildren = $children->isNotEmpty();

    $depthClass  = match(true) {
        $depth === 0 => 'dept-tree-node--depth-0',
        $depth === 1 => 'dept-tree-node--depth-1',
        $depth === 2 => 'dept-tree-node--depth-2',
        default      => 'dept-tree-node--depth-3',
    };

    $type        = $record->type;
    $typeLabel   = $type ? $type->getLabel() : null;
    $typeColor   = $type ? ($type->getColor() ?? 'info') : 'info';

    $status      = $record->status;
    $isActive    = (bool) $status;

    $editUrl = \App\Filament\Resources\Departments\DepartmentResource::getUrl('edit', ['record' => $record]);
    $createUrl = \App\Filament\Resources\Departments\DepartmentResource::getUrl('create', ['record' => $record]);

    $positionsUrl = route('filament.admin.resources.positions.index', [
        'filters[department_id][value]'            => $record->getKey(),
        'filters[hide_scheduled_dismissals][isActive]' => true,
    ]);

    /** @var array $descendantCounts */
    $descendantCounts = app(\App\Services\DepartmentDescendantTypeCountService::class)
        ->getCachedDescendantTypeCountsPayload($record);
@endphp

<div
    class="dept-tree-node {{ $depthClass }}"
    role="treeitem"
    aria-expanded="{{ $hasChildren ? 'true' : 'false' }}"
    x-data="treeNode({{ $hasChildren ? 'true' : 'false' }})"
>
    {{-- Row --}}
    <div
        class="dept-tree-node__row"
        x-bind:class="{ 'dept-tree-node__row--collapsed': !open }"
        x-on:click="toggle()"
    >
        {{-- Depth accent bar is applied via CSS on the wrapper --}}

        {{-- Chevron / spacer --}}
        <div class="dept-tree-node__chevron-wrap">
            @if ($hasChildren)
                <span
                    class="dept-tree-node__chevron"
                    data-tree-chevron
                    x-bind:class="{ 'dept-tree-node__chevron--collapsed': !open }"
                    data-tree-toggle
                    x-bind:aria-expanded="open"
                >
                    <x-heroicon-o-chevron-down class="dept-tree-node__chevron-icon" />
                </span>
            @else
                <span class="dept-tree-node__chevron-spacer"></span>
            @endif
        </div>

        {{-- Status dot --}}
        <span
            class="dept-tree-node__status-dot"
            x-tooltip="{{ $isActive ? __('filament.resources.departments.active') : __('filament.resources.departments.archived') }}"
            aria-label="{{ $isActive ? __('filament.resources.departments.active') : __('filament.resources.departments.archived') }}"
        >
            @if ($isActive)
                <x-heroicon-o-check-circle class="dept-tree-node__status-icon dept-tree-node__status-icon--active" />
            @else
                <x-heroicon-o-archive-box class="dept-tree-node__status-icon dept-tree-node__status-icon--archived" />
            @endif
        </span>

        {{-- Index --}}
        @if ($record->index)
            <span class="dept-tree-node__index">{{ $record->index }}</span>
        @endif

        {{-- Name (link) --}}
        <a
            href="{{ $positionsUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="dept-tree-node__name"
            x-on:click.stop
        >
            {{ \Illuminate\Support\Str::limit($record->name, 60, '...') }}
        </a>

        {{-- Spacer --}}
        <div class="dept-tree-node__spacer"></div>

        {{-- Order number --}}
        <span class="dept-tree-node__order">{{ $record->order }}</span>

        {{-- Type badge --}}
        @if ($typeLabel)
            <span class="dept-tree-node__badge dept-tree-node__badge--{{ $typeColor }}">
                {{ $typeLabel }}
            </span>
        @endif

        {{-- Descendant type counts --}}
        @if (!empty($descendantCounts))
            <div class="dept-tree-node__counts" x-on:click.stop>
                @foreach ($descendantCounts as $countItem)
                    <span
                        class="dept-tree-node__count-badge dept-tree-node__count-badge--{{ $countItem['color'] ?? 'gray' }}"
                        title="{{ $countItem['label'] }}"
                    >
                        {{ $countItem['label'] }}: {{ $countItem['count'] }}
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Actions --}}
        <div class="dept-tree-node__actions" x-on:click.stop>
            <a
                href="{{ $createUrl }}"
                class="dept-tree-node__action-btn"
                title="{{ __('filament.resources.departments.add_child') }}"
                aria-label="{{ __('filament.resources.departments.add_child') }}"
            >
                <x-heroicon-o-plus class="dept-tree-node__action-icon" />
            </a>
            <a
                href="{{ $editUrl }}"
                class="dept-tree-node__action-btn"
                title="{{ __('filament.resources.departments.edit') }}"
                aria-label="{{ __('filament.resources.departments.edit') }}"
            >
                <x-heroicon-o-pencil class="dept-tree-node__action-icon" />
            </a>
        </div>
    </div>

    {{-- Children --}}
    @if ($hasChildren)
        <div
            class="dept-tree-node__children"
            data-tree-children
            x-show="open"
            x-transition:enter="dept-tree-node__children--entering"
            x-transition:leave="dept-tree-node__children--leaving"
            role="group"
        >
            @foreach ($children->sortBy('order') as $child)
                <x-filament.resources.departments.components.tree-node
                    :record="$child"
                    :depth="$depth + 1"
                    :all-records="$allRecords"
                />
            @endforeach
        </div>
    @endif
</div>
