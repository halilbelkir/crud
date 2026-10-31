<x-crudPackage::emails.layout :title="$template['heading']" :preview="$template['preview']" :mail="$message ?? null">

    @if (filled($template['heading']))
        <div style="font-family:{!! crudBrand('font.heading') !!}; font-size:26px; line-height:1.2; margin:0 0 14px;">
            {{ $template['heading'] }}
        </div>
    @endif

    @if (filled($template['content']))
        <div style="font-size:15px; line-height:1.6; color:{{ crudBrand('color.muted') }}; margin:0 0 26px;">
            {!! $template['content'] !!}
        </div>
    @endif

    @if ($url && filled($template['button']))
        <table role="presentation" cellpadding="0" cellspacing="0">
            <tr>
                <td class="button" style="background:{{ crudBrand('color.primary') }}; border-radius:30px;">
                    <a href="{{ $url }}" style="display:inline-block; padding:18px 34px; font-family:{!! crudBrand('font.label') !!}; font-size:13px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:#FFFFFF; text-decoration:none;">
                        {{ $template['button'] }}
                    </a>
                </td>
            </tr>
        </table>
    @endif

    @if (filled($template['note']))
        <div class="note" style="background:{{ crudBrand('color.app') }}; border-radius:24px; padding:20px 22px; margin:28px 0 0; font-size:14px; line-height:1.6; color:{{ crudBrand('color.muted') }};">
            {!! $template['note'] !!}
        </div>
    @endif

    @if ($url)
        <div style="font-size:13px; line-height:1.6; color:{{ crudBrand('color.muted') }}; margin:24px 0 0; word-break:break-all;">
            Butona tıklamada sorun yaşıyorsanız aşağıdaki adresi kopyalayıp tarayıcınıza yapıştırın:<br>
            <a href="{{ $url }}" style="color:{{ crudBrand('color.primary') }};">{{ $url }}</a>
        </div>
    @endif

</x-crudPackage::emails.layout>
