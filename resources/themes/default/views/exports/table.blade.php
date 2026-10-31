@php
    $colors  = crudBrand('color');
    $logo    = crudBrand('logo.path');
    $hasLogo = $logo && is_file($logo);
@endphp

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 120px 32px 70px; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            color: {{ $colors['ink'] }};
            background: #FFFFFF;
            margin: 0;
        }

        .card {
            background: #FFFFFF;
            border: 1px solid {{ $colors['line'] }};
            border-radius: 30px;
            padding: 6px 0;
        }

        header {
            position: fixed;
            top: -95px;
            left: 0;
            right: 0;
            height: 62px;
            border-bottom: 3px solid {{ $colors['primary'] }};
            padding-bottom: 10px;
        }

        header table, footer table { width: 100%; border-collapse: collapse; }
        header td, footer td { vertical-align: middle; padding: 0; }
        header .info, footer .right { text-align: right; }

        header .name {
            font-size: 15px;
            font-weight: bold;
            color: {{ $colors['primary'] }};
        }

        header .title { font-size: 15px; font-weight: bold; }

        header .meta { font-size: 8.5px; color: {{ $colors['muted'] }}; margin-top: 3px; }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            border-top: 1px solid {{ $colors['line'] }};
            padding-top: 8px;
            font-size: 8px;
            color: {{ $colors['muted'] }};
        }

        footer .right { float: right; }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            background: {{ $colors['subtle'] }};
            color: {{ $colors['muted'] }};
            font-size: 8px;
            letter-spacing: 0.08em;
            text-align: left;
            padding: 10px 18px;
            border-bottom: 1px solid {{ $colors['line'] }};
        }

        thead th:first-child { border-top-left-radius: 24px; border-bottom-left-radius: 24px; }
        thead th:last-child  { border-top-right-radius: 24px; border-bottom-right-radius: 24px; }

        tbody td {
            padding: 11px 18px;
            border-bottom: 1px solid {{ $colors['line'] }};
        }

        tbody tr:nth-child(even) td { background: {{ $colors['subtle'] }}; }

        tbody tr:last-child td { border-bottom: none; }

        .empty { padding: 24px; text-align: center; color: {{ $colors['muted'] }}; }
    </style>
</head>
<body>

    <header>
        <table>
            <tr>
                <td height="46">
                    @if ($hasLogo)
                        <img src="{{ $logo }}" alt="{{ crudBrand('name') }}" height="34">
                    @else
                        <div class="name">{{ crudBrand('name') }}</div>
                    @endif
                </td>

                <td class="info">
                    <div class="title">{{ $title }}</div>
                    <div class="meta">{{ now()->translatedFormat('d F Y H:i') }} · {{ count($rows) }}@isset($limited) / {{ number_format($limited, 0, ',', '.') }}@endisset kayıt</div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <table>
            <tr>
                <td height="30">{{ crudBrand('name') }}@if (crudBrand('auth.website')) · {{ crudBrand('auth.website') }}@endif</td>
                <td class="right">{{ $title }} · {{ now()->format('d.m.Y') }}</td>
            </tr>
        </table>
    </footer>

    <div class="card">
    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ trUpper($heading) }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="{{ max(1, count($headings)) }}">Kayıt bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

</body>
</html>
