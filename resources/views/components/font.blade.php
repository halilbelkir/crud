@if(!empty(settings('font')))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', settings('font')) }}:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body
        {
            font-family: '{{ settings('font') }}', Inter, Helvetica, sans-serif !important;
        }
    </style>
@endif
