@php
    $user = auth()->user();
@endphp

<header class="lg:hidden shrink-0 bg-card border-b border-line px-5 pb-3.5 pt-[calc(0.875rem+env(safe-area-inset-top))] flex items-center gap-3">
    <a href="{{ route('dashboard') }}">
        <img src="{{ asset(crudBrand('logo.horizontal')) }}" alt="{{ crudBrand('name') }}" class="w-[132px]">
    </a>

    <a href="{{ route('profile.edit') }}" class="ml-auto">
        <x-crudPackage::ui.avatar :name="$user?->name ?? ''" :size="38" tone="primary" />
    </a>
</header>
