@props([
    'code' => 500,
])

@php
    $fallback = config('crudPanel.errors.default');
    $preset   = config('crudPanel.errors.codes', [])[(int) $code] ?? [];

    $icon    = $preset['icon'] ?? $fallback['icon'];
    $title   = $preset['title'] ?? $fallback['title'];
    $message = $preset['message'] ?? $fallback['message'];
@endphp

@auth
    <x-crudPackage::layouts.app
        :title="$title"
        :breadcrumb="[['label' => $fallback['code']], ['label' => $code]]"
    >
        <x-crudPackage::ui.card class="min-w-0 min-h-full" flush fill>
            <x-crudPackage::errors.body
                :code="$code"
                :icon="$icon"
                :title="$title"
                :message="$message"
                :href="route('dashboard')"
                label="Panele dön"
            />
        </x-crudPackage::ui.card>
    </x-crudPackage::layouts.app>
@else
    <x-crudPackage::layouts.guest :title="$title">
        <x-crudPackage::errors.body
            :code="$code"
            :icon="$icon"
            :title="$title"
            :message="$message"
            :href="url('/')"
            label="Giriş sayfasına dön"
            stacked
        />
    </x-crudPackage::layouts.guest>
@endauth
