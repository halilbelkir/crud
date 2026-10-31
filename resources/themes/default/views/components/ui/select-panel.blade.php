@props([
    'withText'   => false,
    'emptyLabel' => null,
])

@if ($withText)
    <div class="px-3 py-2.5 border-b border-line-soft" x-show="searchable">
        <label class="h-ctl rounded-[20px] border border-line-2 flex items-center gap-2.5 px-3.5 focus-within:border-line-3">
            <x-crudPackage::ui.icon name="search" :size="15" class="text-disabled shrink-0" />

            <input
                type="search"
                x-ref="search"
                autocomplete="off"
                placeholder="Ara"
                @input="search($event.target.value)"
                @keydown.down.prevent="move(1)"
                @keydown.up.prevent="move(-1)"
                @keydown.enter.prevent="pick()"
                @keydown.tab="hide()"
                class="flex-1 min-w-0 bg-transparent text-sm text-ink placeholder:text-faint outline-none"
            >
        </label>
    </div>
@endif

<div x-ref="list" class="max-h-[240px] overflow-y-auto overscroll-contain py-1">
    @if ($emptyLabel !== null)
        <template x-if="! query">
            <div
                @click="choose(null)"
                class="px-4 py-2.5 text-sm cursor-pointer flex items-center gap-2.5 hover:bg-subtle"
                :class="empty && 'text-primary font-medium'"
            >
                <span class="w-2 h-2 rounded-full shrink-0" :class="empty ? 'bg-primary' : 'bg-line-3'"></span>
                {{ $emptyLabel }}
            </div>
        </template>
    @endif

    <template x-for="(item, index) in filtered" :key="item.value">
        <div
            @click="choose(item)"
            @mousemove="active = index"
            class="px-4 py-2.5 text-sm cursor-pointer flex items-start gap-2.5"
            :class="{
                'bg-tint-primary text-primary font-medium': chosen(item.value),
                'bg-subtle': active === index && ! chosen(item.value),
            }"
        >
            <template x-if="item.icon">
                <span class="shrink-0 flex" :class="chosen(item.value) ? 'text-primary' : 'text-muted'" x-html="item.icon"></span>
            </template>

            <template x-if="! item.icon">
                <span class="w-2 h-2 mt-1.5 rounded-full shrink-0" :class="chosen(item.value) ? 'bg-primary' : 'bg-line-3'"></span>
            </template>

            <span class="min-w-0 flex-1 break-words" x-html="marked(item.label)"></span>
        </div>
    </template>

    <template x-if="filtered.length === 0">
        <div class="px-4 py-3 text-[13px] text-muted">Sonuç bulunamadı</div>
    </template>
</div>
