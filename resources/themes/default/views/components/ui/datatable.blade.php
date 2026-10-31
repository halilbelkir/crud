@props([
    'columns'   => [],
    'rows'      => [],
    'search'    => null,
    'filters'   => [],
    'exportUrl' => true,
    'create'    => null,
    'rowUrl'      => null,
    'heading'     => null,
    'subheading'  => null,
    'recordLabel' => 'kaydı',
    'unit'        => 'kayıttan',
    'dim'         => null,
    'details'     => null,
    'emptyTitle'   => 'Sonuç bulunamadı',
    'emptyMessage' => 'Aramanıza veya filtrelerinize uyan kayıt yok. Filtreleri temizleyip tekrar deneyin.',
])

@php
    $firstRow = null;

    foreach ($rows as $candidate)
    {
        $firstRow = $candidate;

        break;
    }

    $sortField     = \crudPackage\Support\Table::sortColumn($columns);
    $sortDirection = \crudPackage\Support\Table::sortDirection();

    $sortAlias = $sortField === null && $firstRow instanceof \Illuminate\Database\Eloquent\Model && \crudPackage\Support\Sortable::supports($firstRow)
        ? \crudPackage\Support\Sortable::alias($firstRow)
        : null;

    $sortUrl = $sortAlias ? route('reorder', array_filter(['locale' => request()->route('locale')])) : null;

    $gridCols = collect($columns)
        ->map(function ($column)
        {
            if (isset($column['width']))
            {
                return $column['width'];
            }

            return match ($column['type'] ?? 'text')
            {
                'check'   => '44px',
                'expand'  => '44px',
                'actions' => '60px',
                default   => '1fr',
            };
        })
        ->implode(' ');

    $gridCols = $sortAlias ? '44px ' . $gridCols : $gridCols;

    $resolve = static function ($column, $key, $row, $default = null)
    {
        $value = $column[$key] ?? $default;

        return $value instanceof \Closure ? $value($row) : $value;
    };

    $isPaginator = $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $hasRows     = $isPaginator ? $rows->count() > 0 : count($rows) > 0;
    $hasFilters  = count($filters) > 0 || $search;

    $clearable = collect($filters)
        ->reject(fn ($filter) => $filter['context'] ?? false)
        ->pluck('name')
        ->when($search, fn ($names) => $names->push($search['name']));

    $activeFilters = $clearable->filter(fn ($name) => filled(request($name)))->values();
    $clearUrl      = request()->fullUrlWithoutQuery($activeFilters->merge(['page'])->all());

    $creates        = $create ? (array_is_list($create) ? $create : [$create]) : [];
    $hasToolbar     = $hasFilters || $exportUrl || $creates;
    $hasFooter      = $isPaginator;
    $leadColumn     = collect($columns)->firstWhere('type', 'lead');
    $rowTitle       = fn ($row) => $leadColumn ? ($resolve($leadColumn, 'title', $row) ?? $resolve($leadColumn, 'name', $row)) : null;
    $statusColumn   = collect($columns)->firstWhere('type', 'status');
    $progressColumn = collect($columns)->firstWhere('type', 'progress');
    $actionsColumn  = collect($columns)->firstWhere('type', 'actions');
    $checkColumn    = collect($columns)->firstWhere('type', 'check');
    $fieldColumns   = collect($columns)->where('type', 'field');
    $subheading     = $subheading ?? ($isPaginator ? $rows->total() . ' kayıt' : null);
    $declaredMobile = collect($columns)->filter(fn ($column) => $column['mobile'] ?? false);

    $metaColumns = $declaredMobile->isNotEmpty()
        ? $declaredMobile
        : collect($columns)->filter(fn ($column) => in_array($column['type'] ?? 'text', ['text', 'badge'], true))->take(3);
@endphp

<div class="flex flex-col gap-2.5 min-h-0 lg:contents">

@if ($heading)
    <div data-refresh="heading" class="lg:hidden flex items-end gap-3 shrink-0">
        <div class="min-w-0">
            <div class="font-heading text-[22px] leading-[1.1] truncate">{{ $heading }}</div>

            @if ($subheading)
                <div class="text-[12.5px] text-muted mt-0.5 truncate">{{ $subheading }}</div>
            @endif
        </div>

        @if ($creates)
            <span class="ml-auto shrink-0 flex items-center gap-2">
                @foreach ($creates as $action)
                    <x-crudPackage::ui.datatable.create :create="$action" compact />
                @endforeach
            </span>
        @endif
    </div>
@endif

<div data-datatable {{ $attributes->class('bg-card border border-line rounded-card flex flex-col') }}>

    @if ($hasToolbar)
        <div class="lg:hidden px-3.5 py-3 border-b border-line-soft flex items-center gap-2">
            @if ($search)
                <x-crudPackage::ui.datatable.search :search="$search" compact />
            @endif

            <x-crudPackage::ui.datatable.sort :columns="$columns" />

            @if ($exportUrl)
                <x-crudPackage::ui.datatable.export compact />
            @endif
        </div>

        @if ($hasFilters || $isPaginator)
            <div data-refresh="filters" class="lg:hidden px-3.5 py-2.5 border-b border-line-soft flex items-center gap-2 flex-wrap">
                <x-crudPackage::ui.datatable.filters :filters="$filters" :per-page="$isPaginator" compact />

                @if ($activeFilters->isNotEmpty())
                    <a href="{{ $clearUrl }}" x-data @click.prevent="window.clearFilters(@js($activeFilters))" class="font-label text-[11px] font-semibold text-muted whitespace-nowrap flex-none">Temizle</a>
                @endif
            </div>
        @endif

        <div class="hidden lg:flex px-5 py-4 border-b border-line-soft items-center gap-3 flex-wrap">
            @if ($search)
                <x-crudPackage::ui.datatable.search :search="$search" />
            @endif

            <x-crudPackage::ui.datatable.filters :filters="$filters" :per-page="$isPaginator" />

            <span data-refresh="clear" class="contents">
                @if ($activeFilters->isNotEmpty())
                    <a href="{{ $clearUrl }}" x-data @click.prevent="window.clearFilters(@js($activeFilters))" class="font-label text-xs font-semibold text-muted hover:text-ink flex-none">Filtreleri temizle</a>
                @endif
            </span>

            <div class="ml-auto flex items-center gap-2.5 flex-none">
                @if ($exportUrl)
                    <x-crudPackage::ui.datatable.export />
                @endif

                @foreach ($creates as $action)
                    <x-crudPackage::ui.datatable.create :create="$action" />
                @endforeach
            </div>
        </div>
    @endif

    <div data-refresh="rows" class="contents">

    @if ($hasRows)
        <div class="hidden lg:block">
            <div
                @class([
                    'grid items-center gap-x-6 px-5 h-[46px] bg-subtle border-b border-line-soft label-caps text-[11px]',
                    'rounded-t-card' => ! $hasToolbar,
                ])
                style="grid-template-columns: {{ $gridCols }}"
            >
                @if ($sortAlias)
                    <span></span>
                @endif

                @foreach ($columns as $column)
                    @php
                        $field  = is_string($column['sortable'] ?? null) ? $column['sortable'] : null;
                        $sorted = $field !== null && $field === $sortField;
                    @endphp

                    <span @class([
                        'flex items-center gap-1.5',
                        'justify-end text-right' => ($column['align'] ?? null) === 'right',
                    ])>
                        @if ($field)
                            <button
                                type="button"
                                x-data
                                @click="window.applyParams({ sirala: @js($field), yon: @js($sorted && $sortDirection === 'asc' ? 'desc' : 'asc') })"
                                @class([
                                    'flex items-center gap-1.5 transition-colors',
                                    'text-primary' => $sorted,
                                    'hover:text-ink' => ! $sorted,
                                ])
                            >
                                {{ $column['label'] ?? '' }}

                                <x-crudPackage::ui.icon
                                    :name="$sorted ? ($sortDirection === 'asc' ? 'chevron-up' : 'chevron-down') : 'sort'"
                                    :size="$sorted ? 13 : 12"
                                    :stroke="2.4"
                                    :class="$sorted ? '' : 'text-disabled'"
                                />
                            </button>
                        @else
                            {{ $column['label'] ?? '' }}
                        @endif
                    </span>
                @endforeach
            </div>

            <div @if ($sortAlias) x-data x-init="window.initSorting($el, @js($sortUrl), @js($sortAlias))" @endif>
                @foreach ($rows as $row)
                    <div @if ($details) x-data="{ open: false }" @endif class="border-b border-line-softer last:border-b-0">
                        <div
                            @if ($sortAlias)
                                data-row="{{ $row->getKey() }}"
                            @endif
                            @class([
                                'grid items-center gap-x-6 px-5 min-h-16 text-sm hover:bg-subtle',
                                'opacity-[0.62]' => $dim instanceof \Closure && $dim($row),
                            ])
                            style="grid-template-columns: {{ $gridCols }}"
                        >
                            @if ($sortAlias)
                                <span data-drag class="flex items-center text-disabled cursor-grab active:cursor-grabbing touch-none">
                                    <x-crudPackage::ui.icon name="grip" :size="18" />
                                </span>
                            @endif

                            @foreach ($columns as $column)
                                <x-crudPackage::ui.datatable.cell
                                    :column="$column"
                                    :row="$row"
                                    :row-title="$rowTitle($row)"
                                    :record-label="$recordLabel"
                                />
                            @endforeach
                        </div>

                        @if ($details)
                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition duration-150"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="border-t border-line-softer bg-subtle"
                            >
                                {{ $details($row) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lg:hidden" @if ($sortAlias) x-data x-init="window.initSorting($el, @js($sortUrl), @js($sortAlias))" @endif>
            @foreach ($rows as $row)
                <div
                    @if ($sortAlias)
                        data-row="{{ $row->getKey() }}"
                    @endif
                    @class([
                        'px-3.5 py-3 border-b border-line-softer flex flex-col gap-[9px] last:border-b-0',
                        'opacity-[0.62]' => $dim instanceof \Closure && $dim($row),
                    ])
                >
                    <div class="flex items-center gap-[11px]">
                        @if ($sortAlias)
                            <span data-drag class="flex items-center shrink-0 text-disabled cursor-grab active:cursor-grabbing touch-none">
                                <x-crudPackage::ui.icon name="grip" :size="18" />
                            </span>
                        @endif

                        @if ($checkColumn)
                            <x-crudPackage::ui.datatable.cell :column="$checkColumn" :row="$row" />
                        @endif

                        @if ($leadColumn)
                            <x-crudPackage::ui.avatar
                                :name="$resolve($leadColumn, 'name', $row, '')"
                                :image="$resolve($leadColumn, 'image', $row)"
                                :contain="$leadColumn['logo'] ?? false"
                                :chip="$leadColumn['logo'] ?? false"
                                :lead="$leadColumn['initials'] ?? false"
                                :size="36"
                            />

                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium truncate">{{ $resolve($leadColumn, 'name', $row) }}</span>
                                <span class="block text-xs text-muted truncate">{{ $resolve($leadColumn, 'sub', $row) }}</span>
                            </span>
                        @endif

                        @if ($statusColumn)
                            <x-crudPackage::ui.status :tone="$resolve($statusColumn, 'tone', $row, 'success')" class="shrink-0 text-[12.5px]">
                                {{ $resolve($statusColumn, 'value', $row) }}
                            </x-crudPackage::ui.status>
                        @endif

                        @if ($actionsColumn)
                            <x-crudPackage::ui.datatable.cell
                                :column="$actionsColumn"
                                :row="$row"
                                :row-title="$rowTitle($row)"
                                :record-label="$recordLabel"
                                compact
                            />
                        @endif
                    </div>

                    @if ($metaColumns->isNotEmpty() || $progressColumn)
                        <div class="flex items-center gap-3 border-t border-line-softer pt-2 text-[12.5px] text-muted">
                            @foreach ($metaColumns as $column)
                                @switch ($column['type'] ?? 'text')
                                    @case ('badge')
                                        <x-crudPackage::ui.badge :tone="$resolve($column, 'tone', $row, 'accent')" class="!h-[22px] !text-[10.5px]">
                                            {{ $resolve($column, 'value', $row) }}
                                        </x-crudPackage::ui.badge>

                                        @break

                                    @case ('text')
                                        <span class="truncate">{{ $resolve($column, 'value', $row) }}</span>

                                        @break

                                    @default
                                        <x-crudPackage::ui.datatable.cell
                                            :column="$column"
                                            :row="$row"
                                            :row-title="$rowTitle($row)"
                                            :record-label="$recordLabel"
                                        />
                                @endswitch
                            @endforeach

                            @if ($progressColumn)
                                @php $percent = (int) $resolve($progressColumn, 'percent', $row, 0); @endphp

                                <span class="ml-auto flex items-center gap-2 shrink-0">
                                    <span class="font-label font-semibold text-ink">{{ $resolve($progressColumn, 'value', $row) }}</span>
                                    <span class="w-[46px] h-1.5 rounded-[3px] bg-line-soft overflow-hidden">
                                        <span class="block h-full rounded-[3px] bg-primary" style="width: {{ $percent }}%"></span>
                                    </span>
                                </span>
                            @endif
                        </div>
                    @endif

                    @if ($fieldColumns->isNotEmpty())
                        <div class="flex flex-col gap-2.5 border-t border-line-softer pt-2.5">
                            @foreach ($fieldColumns as $column)
                                <div class="flex flex-col gap-1.5">
                                    <span class="label-caps">{{ $column['label'] ?? '' }}</span>

                                    <x-crudPackage::ui.datatable.cell :column="$column" :row="$row" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($isPaginator)
            <x-crudPackage::ui.pagination :rows="$rows" :unit="$unit" class="lg:border-t lg:border-line-soft" />
        @endif
    @else
        <x-crudPackage::ui.empty-state :title="$emptyTitle" :message="$emptyMessage">
            @if ($activeFilters->isNotEmpty())
                <x-slot:action>
                    <x-crudPackage::ui.button :href="$clearUrl" x-data @click.prevent="window.clearFilters({{ \Illuminate\Support\Js::from($activeFilters) }})" variant="outline" size="sm">Filtreleri temizle</x-crudPackage::ui.button>
                </x-slot:action>
            @endif
        </x-crudPackage::ui.empty-state>
    @endif

    </div>

</div>

</div>
