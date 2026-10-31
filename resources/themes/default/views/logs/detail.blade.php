@php
    use crudPackage\Support\ActivityLog;

    $title   = ActivityLog::subject($log);
    $updated = ActivityLog::updated($log->description);

    $meta = [
        ActivityLog::table($log->log_name),
        $log->causer?->name ?? 'Sistem',
        $log->created_at?->translatedFormat('d F Y H:i'),
    ];
@endphp

<x-crudPackage::layouts.app
    :title="$title"
    :breadcrumb="[['label' => 'Geçmiş', 'url' => route('logs.index')], ['label' => $title]]"
>
    <div class="flex flex-col gap-3.5">

        <x-crudPackage::ui.detail-header
            :title="$title"
            :status="ActivityLog::label($log->description)"
            :status-tone="ActivityLog::tone($log->description)"
            :meta="$meta"
        />

        <div class="grid lg:grid-cols-[380px_1fr] gap-3.5 items-start">
            <x-crudPackage::ui.card title="Log bilgileri" flush>
                <div class="px-[22px] py-2">
                    <x-crudPackage::ui.detail-row label="İşlem">
                        <x-crudPackage::ui.badge :tone="ActivityLog::tone($log->description)">{{ ActivityLog::label($log->description) }}</x-crudPackage::ui.badge>
                    </x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Bölüm">{{ ActivityLog::table($log->log_name) }}</x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Kayıt">{{ $title }}</x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Kullanıcı">{{ $log->causer?->name ?? 'Sistem' }}</x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Tarih">{{ $log->created_at?->format('d.m.Y H:i:s') }}</x-crudPackage::ui.detail-row>
                </div>
            </x-crudPackage::ui.card>

            <x-crudPackage::ui.card
                :title="$updated ? 'Değişen alanlar' : 'Kayıt içeriği'"
                :subtitle="$updated ? 'Yalnızca değeri değişen alanlar listelenir' : 'İşlem anındaki alan değerleri'"
                flush
            >
                <div class="px-[22px] py-4">
                    @if ($updated)
                        @forelse ($changes as $field => $change)
                            <div class="py-3 border-b border-line-softer last:border-b-0">
                                <div class="label-caps mb-2">{{ ActivityLog::field($log->log_name, $field) }}</div>

                                <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
                                    <span
                                        class="flex-1 min-w-0 rounded-alert bg-tint-danger text-on-tint-danger px-4 py-2.5 text-sm break-words whitespace-pre-line"
                                    >{{ $change['old'] }}</span>

                                    <x-crudPackage::ui.icon name="chevron-right" :size="16" class="shrink-0 text-disabled rotate-90 sm:rotate-0" />

                                    <span
                                        class="flex-1 min-w-0 rounded-alert bg-tint-success text-on-tint-success px-4 py-2.5 text-sm break-words whitespace-pre-line"
                                    >{{ $change['new'] }}</span>
                                </div>
                            </div>
                        @empty
                            <x-crudPackage::ui.alert tone="neutral">Bu işlemde alan değeri değişmemiş.</x-crudPackage::ui.alert>
                        @endforelse
                    @else
                        @forelse ($snapshot as $field => $value)
                            <x-crudPackage::ui.detail-row :label="ActivityLog::field($log->log_name, $field)">{{ $value }}</x-crudPackage::ui.detail-row>
                        @empty
                            <x-crudPackage::ui.alert tone="neutral">Kayıt içeriği tutulmamış.</x-crudPackage::ui.alert>
                        @endforelse
                    @endif
                </div>
            </x-crudPackage::ui.card>
        </div>

    </div>
</x-crudPackage::layouts.app>
