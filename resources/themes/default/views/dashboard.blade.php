<x-crudPackage::layouts.app
    title="Dashboard"
    :breadcrumb="[['label' => 'Dashboard'], ['label' => 'Genel görünüm']]"
>
    @php
        $hour     = now()->hour;
        $greeting = $hour < 12 ? 'Günaydın' : ($hour < 18 ? 'İyi günler' : 'İyi akşamlar');
    @endphp

    <div class="flex flex-col gap-3 lg:gap-3.5">

        <div class="lg:hidden">
            <div class="font-label text-[11px] tracking-[0.22em] uppercase text-muted">{{ now()->translatedFormat('d F · l') }}</div>
            <div class="font-heading text-[26px] mt-1">{{ $greeting }}, {{ auth()->user()->name }}</div>
        </div>

    </div>
</x-crudPackage::layouts.app>
