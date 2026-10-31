@props([
    'title'   => null,
    'preview' => null,
    'mail'    => null,
])

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>{{ $title ?? crudBrand('name') }}</title>

    <style>
        :root {
            color-scheme: light only;
            supported-color-schemes: light only;
        }

        @media (prefers-color-scheme: dark) {
            .page  { background: {{ crudBrand('color.app') }} !important; }
            .card   { background: #FFFFFF !important; }
            .band   { background: {{ crudBrand('color.primary') }} !important; }
            .content  { background: #FFFFFF !important; color: {{ crudBrand('color.ink') }} !important; }
            .footer { background: #FFFFFF !important; color: {{ crudBrand('color.muted') }} !important; }
            .content h1, .content h2, .content strong, .content div, .content span { color: {{ crudBrand('color.ink') }} !important; }
            .button  { background: {{ crudBrand('color.primary') }} !important; }
            .button a { color: #FFFFFF !important; }
            .note   { background: {{ crudBrand('color.app') }} !important; color: {{ crudBrand('color.muted') }} !important; }
        }

        u + .page .content,
        [data-ogsc] .content { background: #FFFFFF !important; color: {{ crudBrand('color.ink') }} !important; }
    </style>
</head>
<body class="page" style="margin:0; padding:0; background:{{ crudBrand('color.app') }}; font-family:{!! crudBrand('font.body') !!}; color:{{ crudBrand('color.ink') }};">

    @if ($preview)
        <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $preview }}</div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="page" style="background:{{ crudBrand('color.app') }}; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" class="card" style="width:560px; max-width:100%; background:#FFFFFF; border-radius:30px; overflow:hidden;">

                    <tr>
                        <td align="center" class="band" style="background:{{ crudBrand('color.primary') }}; padding:34px 36px;">
                            @php
                                $logo   = $mail ? \crudPackage\Support\Brand::lightLogo() : null;
                                $source = $logo ? $mail->embedData($logo, 'logo.png', 'image/png') : crudBrand('logo.horizontal_light');
                            @endphp

                            <img src="{{ $source }}" alt="{{ crudBrand('name') }}" width="190" style="display:block; width:190px; margin:0 auto;">
                        </td>
                    </tr>

                    <tr>
                        <td class="content" style="padding:36px; background:#FFFFFF; color:{{ crudBrand('color.ink') }};">
                            {{ $slot }}
                        </td>
                    </tr>

                    <tr>
                        <td class="footer" style="border-top:1px solid {{ crudBrand('color.line') }}; padding:22px 36px; font-size:13px; line-height:1.6; background:#FFFFFF; color:{{ crudBrand('color.muted') }};">
                            Bu e-posta {{ crudBrand('name') }} tarafından gönderildi.
                            @if (crudBrand('auth.website'))
                                <br>{{ crudBrand('auth.website') }}
                            @endif
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
