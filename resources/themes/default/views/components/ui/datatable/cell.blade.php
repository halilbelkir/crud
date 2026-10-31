@props([
    'column',
    'row',
    'rowTitle'    => null,
    'recordLabel' => 'kaydı',
    'compact'     => false,
])

@php
    $type = $column['type'] ?? 'text';

    $resolve = static function ($key, $default = null) use ($column, $row)
    {
        $value = $column[$key] ?? $default;

        return $value instanceof \Closure ? $value($row) : $value;
    };
@endphp

@switch ($type)

    @case ('expand')
        <span class="flex items-center">
            <button
                type="button"
                @click="open = ! open"
                class="w-7 h-7 rounded-full flex items-center justify-center text-muted hover:bg-line-soft hover:text-ink transition-colors"
            >
                <x-crudPackage::ui.icon name="chevron-down" :size="16" class="transition-transform" ::class="open && 'rotate-180 text-primary'" />
            </button>
        </span>
        @break

    @case ('check')
        <span class="relative flex items-center">
            <input
                type="checkbox"
                name="selected[]"
                value="{{ $resolve('value') }}"
                class="peer absolute inset-0 m-0 w-full h-full opacity-0 appearance-none cursor-pointer"
            >
            <span class="shrink-0 w-[18px] h-[18px] rounded-md border-2 border-line-3 flex items-center justify-center text-white transition-colors peer-checked:bg-primary peer-checked:border-primary peer-checked:[&>svg]:opacity-100">
                <x-crudPackage::ui.icon name="check" :size="11" :stroke="3" class="opacity-0" />
            </span>
        </span>
        @break

    @case ('lead')
        <span class="flex items-center gap-3 min-w-0">
            @unless ($column['noAvatar'] ?? false)
                <x-crudPackage::ui.avatar
                    :name="$resolve('name', '')"
                    :image="$resolve('image')"
                    :tone="$resolve('tone', 'accent')"
                    :contain="$column['logo'] ?? false"
                    :chip="$column['logo'] ?? false"
                    :lead="$column['initials'] ?? false"
                />
            @endunless

            <span class="min-w-0">
                <span class="block font-medium truncate">
                    {{ $resolve('name') }}

                    @if ($tag = $resolve('tag'))
                        <span class="font-label text-[10px] font-semibold text-accent tracking-[0.08em]">{{ $tag }}</span>
                    @endif
                </span>

                @if ($sub = $resolve('sub'))
                    <span class="block text-xs text-muted truncate">{{ $sub }}</span>
                @endif
            </span>
        </span>
        @break

    @case ('badge')
        <span class="flex items-center">
            <x-crudPackage::ui.badge :tone="$resolve('tone', 'accent')">{{ $resolve('value') }}</x-crudPackage::ui.badge>
        </span>
        @break

    @case ('pill')
        <span class="flex items-center">
            <x-crudPackage::ui.pill :tone="$resolve('tone', 'neutral')" :icon="$resolve('icon')">{{ $resolve('value') }}</x-crudPackage::ui.pill>
        </span>
        @break

    @case ('status')
        <span class="flex items-center">
            <x-crudPackage::ui.status :tone="$resolve('tone', 'success')">{{ $resolve('value') }}</x-crudPackage::ui.status>
        </span>
        @break

    @case ('progress')
        @php $percent = (int) $resolve('percent', 0); @endphp
        <span class="flex items-center gap-2">
            <span class="font-label font-semibold">{{ $resolve('value') }}</span>
            <span class="w-14 h-1.5 rounded-[3px] bg-line-soft overflow-hidden">
                <span class="block h-full rounded-[3px] {{ $percent > 0 ? 'bg-primary' : 'bg-line-soft' }}" style="width: {{ $percent }}%"></span>
            </span>
        </span>
        @break

    @case ('avatars')
        @php
            $ringTones = [
                'success' => 'border-success',
                'accent'  => 'border-accent',
                'primary' => 'border-primary',
                'danger'  => 'border-danger',
                'muted'   => 'border-line-2 opacity-70',
            ];
        @endphp

        <span class="flex items-center gap-1.5 min-w-0">
            <span class="flex shrink-0 isolate">
                @php
                    $avatars = (array) $resolve('items', []);
                @endphp

                @foreach ($avatars as $index => $item)
                    <span
                        @class([
                            'relative w-[30px] h-[30px] shrink-0 rounded-full bg-white border-2 flex items-center justify-center',
                            '-ml-2' => $index > 0,
                            ($item['faded'] ?? false) ? 'border-line-2' : ($ringTones[$item['tone'] ?? 'muted'] ?? $ringTones['muted']),
                        ])
                        style="z-index: {{ count($avatars) - $index }}"
                    >
                        <img src="{{ $item['image'] }}" alt="{{ $item['label'] ?? '' }}" @class(['w-[18px] h-[18px] object-contain', 'opacity-50 grayscale' => $item['faded'] ?? false])>

                        @if ($item['marked'] ?? false)
                            <span class="absolute -right-1 -bottom-1 w-3.5 h-3.5 rounded-full bg-success text-white ring-2 ring-card flex items-center justify-center">
                                <x-crudPackage::ui.icon name="check" :size="9" :stroke="3" />
                            </span>
                        @endif
                    </span>
                @endforeach

                @if ($more = $resolve('more'))
                    <span class="w-[30px] h-[30px] shrink-0 ml-1 rounded-full bg-tint-accent-2 border-2 border-line-2 text-on-tint-accent flex items-center justify-center font-label text-[11px] font-semibold">
                        +{{ $more }}
                    </span>
                @endif
            </span>

            @if ($note = $resolve('note'))
                <span class="text-[12.5px] text-muted whitespace-nowrap">{{ $note }}</span>
            @endif
        </span>
        @break

    @case ('chips')
        @php
            $chipTones = [
                'primary' => 'bg-tint-primary text-primary',
                'success' => 'bg-tint-success text-on-tint-success',
                'accent'  => 'bg-tint-accent-2 text-on-tint-accent',
                'danger'  => 'bg-tint-danger text-on-tint-danger',
                'muted'   => 'bg-neutral text-muted',
                'neutral' => 'bg-neutral text-body',
            ];
        @endphp

        <span class="flex flex-nowrap items-center gap-2 min-w-0 pr-4 overflow-hidden">
            @foreach ((array) $resolve('items', []) as $chip)
                <span class="inline-flex shrink-0 items-center gap-1.5 h-7 rounded-[14px] px-3 text-[13px] whitespace-nowrap {{ $chipTones[$chip['tone'] ?? 'neutral'] ?? $chipTones['neutral'] }}">
                    <strong class="font-semibold">{{ $chip['label'] ?? '' }}</strong>

                    @if (isset($chip['value']))
                        <span class="text-disabled">·</span>
                        <span>{{ $chip['value'] }}</span>
                    @endif
                </span>
            @endforeach

            @if ($more = $resolve('more'))
                <span class="inline-flex shrink-0 items-center h-7 rounded-[14px] px-2.5 text-[13px] bg-neutral text-muted">+{{ $more }}</span>
            @endif
        </span>
        @break

    @case ('field')
        <span
            x-data="inlineField(@js($resolve('url')), @js($column['name'] ?? null))"
            @change="save($event.target)"
            class="block min-w-0 pr-4"
        >
            {!! $resolve('html') !!}
        </span>
        @break

    @case ('loading')
        <span class="inline-flex items-center gap-2.5 text-[13px] text-body">
            <x-crudPackage::ui.loader variant="small" />
            {{ $resolve('value', 'İşleniyor…') }}
        </span>
        @break

    @case ('actions')
        @php
            $menu   = (array) $resolve('menu', []);
            $toggle = $column['status'] ?? true;
            $toggle = $toggle instanceof \Closure ? $toggle($row) : $toggle;

            $statusItem = null;

            if ($toggle && $row instanceof \Illuminate\Database\Eloquent\Model && \crudPackage\Support\Status::allows($row))
            {
                $isActive = \crudPackage\Support\Status::isActive($row);
                $target   = trim(($rowTitle ? $rowTitle . ' isimli ' : '') . $recordLabel);

                $statusItem = [
                    'label'   => $isActive ? 'Pasif Et' : 'Aktif Et',
                    'icon'    => $isActive ? 'minus-circle' : 'check-circle',
                    'href'    => route('status.update'),
                    'method'  => 'POST',
                    'confirm' => [
                        'title'        => $target . ($isActive ? ' pasife almak' : ' aktif etmek') . ' istediğinize emin misiniz?',
                        'message'      => $isActive
                            ? 'Pasif kayıtlar listelerde görünmeye devam eder' . ($row instanceof \crudPackage\Models\User ? ', ilgili kullanıcı giriş yapamaz.' : '.')
                            : 'Kayıt yeniden aktif duruma geçecek.',
                        'tone'         => $isActive ? 'warning' : 'warning',
                        'confirmLabel' => $isActive ? 'Evet, pasife al' : 'Evet, aktif et',
                    ],
                    'payload' => [
                        'model' => \crudPackage\Support\Status::alias($row),
                        'id'    => $row->getKey(),
                    ],
                ];

                $separatorIndex = array_search('separator', $menu, true);

                if ($separatorIndex === false)
                {
                    $menu[] = $statusItem;
                }
                else
                {
                    array_splice($menu, $separatorIndex + 1, 0, [$statusItem]);
                }
            }
        @endphp

        <span class="flex justify-end">
            @if ($menu)
                <x-crudPackage::ui.dropdown>
                    <x-slot:trigger>
                        <button
                            type="button"
                            @class([
                                'flex items-center justify-center transition-colors focus:outline-none',
                                'w-[30px] h-[30px] rounded-[15px]' => $compact,
                                'w-[34px] h-[34px] rounded-full border' => ! $compact,
                            ])
                            :class="open
                                ? '{{ $compact ? 'bg-ink text-white' : 'bg-ink border-ink text-white' }}'
                                : '{{ $compact ? 'bg-app text-body' : 'border-line text-muted hover:bg-app hover:border-line-2 hover:text-ink focus-visible:border-primary focus-visible:text-primary' }}'"
                        >
                            <x-crudPackage::ui.icon name="dots" :size="18" />
                        </button>
                    </x-slot:trigger>

                    @foreach ($menu as $item)
                        @if ($item === 'separator')
                            <x-crudPackage::ui.dropdown-separator />
                        @else
                            @php
                                $confirm = $item['confirm'] ?? null;

                                if ($confirm === true)
                                {
                                    $confirm = [
                                        'title'        => trim(($rowTitle ? $rowTitle . ' isimli ' : '') . $recordLabel . ' kalıcı olarak silmek istediğinize emin misiniz?'),
                                        'message'      => 'Bu işlem geri alınamaz.' . ($statusItem ? ' Kaydı korumak için silmek yerine pasife almayı düşünün.' : ''),
                                        'verify'       => 'SİL',
                                        'confirmLabel' => 'Kalıcı olarak sil',
                                    ];
                                }
                            @endphp

                            <x-crudPackage::ui.dropdown-item
                                :href="$item['href'] ?? null"
                                :icon="$item['icon'] ?? null"
                                :method="$item['method'] ?? null"
                                :danger="$item['danger'] ?? false"
                                :drawer="$item['drawer'] ?? false"
                                :title="$item['title'] ?? null"
                                :confirm="$confirm"
                                :payload="$item['payload'] ?? []"
                                :disabled="$item['disabled'] ?? false"
                                :copy="$item['copy'] ?? null"
                                :copied="$item['copied'] ?? 'Kopyalandı'"
                            >
                                {{ $item['label'] ?? '' }}
                            </x-crudPackage::ui.dropdown-item>
                        @endif
                    @endforeach
                </x-crudPackage::ui.dropdown>
            @endif
        </span>
        @break

    @default
        <span class="flex items-center gap-2 min-w-0 pr-4">
            @if (($sub = $resolve('sub')) || ($column['wrap'] ?? false))
                <span class="min-w-0">
                    <span class="block break-words text-body">{{ $resolve('value') }}</span>

                    @if ($sub)
                        <span class="block text-xs text-muted break-words">{{ $sub }}</span>
                    @endif
                </span>
            @else
                <span @class([
                    'min-w-0 truncate',
                    'font-label font-semibold' => $column['bold'] ?? false,
                    'text-body' => ! ($column['bold'] ?? false),
                ])>{{ $resolve('value') }}</span>
            @endif

            @if ($count = $resolve('count'))
                <x-crudPackage::ui.badge tone="accent" class="h-6 !rounded-xl">{{ $count }}</x-crudPackage::ui.badge>
            @endif

            @if ($flag = $resolve('flag'))
                <x-crudPackage::ui.badge tone="primary" class="h-6 !rounded-xl">{{ $flag }}</x-crudPackage::ui.badge>
            @endif
        </span>

@endswitch
