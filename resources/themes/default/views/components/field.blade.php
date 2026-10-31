@php
    $label    = $attributes->get('label');
    $required = (bool) $attributes->get('required');
    $info     = $attributes->get('info');
    $hidden   = (bool) $attributes->get('hidden');
    $wide     = (bool) $attributes->get('wide');
    $span     = $attributes->get('span');
@endphp

<div data-form-cell @class(['flex flex-col gap-2 min-w-0', 'hidden' => $hidden, '@2xl:col-span-2' => $wide, 'sm:flex-[var(--span)_1_calc(var(--span)*100%/12_-_0.875rem)]' => $span]) @if ($span) style="--span: {{ (int) $span }}" @endif>
    @if ($label && ! $hidden)
        <label class="label-caps" @if ($for = $attributes->get('for')) for="{{ $for }}" @endif>
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($info && ! $hidden)
        <span class="text-xs text-muted [&_a]:text-primary [&_a]:font-medium [&_a:hover]:underline">{!! $info !!}</span>
    @endif
</div>
