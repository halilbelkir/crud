@props([
    'href'  => null,
    'label' => 'Vazgeç',
    'size'  => 'md',
])

<x-crudPackage::ui.button
    type="button"
    variant="outline"
    :size="$size"
    x-data
    @click="$store.dialog.ask({
        title: 'Vazgeçilsin mi?',
        message: 'Girdiğiniz bilgiler kaydedilmeyecek.',
        tone: 'warning',
        confirmLabel: 'Evet, vazgeç',
        action: () => {
            if ($el.closest('[data-form-drawer]')) {
                window.dispatchEvent(new CustomEvent('form-drawer-close'));
                return;
            }

            window.location.assign({{ \Illuminate\Support\Js::from($href ?? url()->previous()) }});
        },
    })"
    {{ $attributes }}
>
    {{ $label }}
</x-crudPackage::ui.button>
