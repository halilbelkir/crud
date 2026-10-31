@props([
    'name'     => null,
    'label'    => null,
    'value'    => null,
    'required' => false,
    'hint'     => null,
    'plain'    => false,
    'height'   => 320,
])

@php
    $error   = $name ? $errors->first(crudFieldKey($name)) : null;
    $content = $value ?? ($name ? old($name) : null);
@endphp

<x-crudPackage::form.field :label="$label" :name="$name" :required="$required" :hint="$hint" :plain="$plain">
    <div
        x-data="editorField({ upload: '{{ route('ckeditor.imageUpload') }}' })"
        style="--editor-height: {{ (int) $height }}px"
        @class(['editor-field', 'editor-field-invalid' => (bool) $error])
    >
        <textarea
            x-ref="source"
            @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
            class="hidden"
        >{{ $content }}</textarea>

        <div x-ref="editor"></div>
    </div>
</x-crudPackage::form.field>
