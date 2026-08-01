<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ settings('title') }} - Şifre Sıfırlama</title>
    <style>
        @if(!empty(settings('font')))
        @import url('https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', settings('font')) }}:wght@400;600;700&display=swap');
        @endif

        :root
        {
            --primaryColor : {{ settings('color_1') }};
            --secondaryColor : {{ settings('color_2') }};
            color-scheme: light;
            supported-color-schemes: light;
        }

        body
        {
            font-family: @if(!empty(settings('font'))) '{{ settings('font') }}', @endif Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container
        {
            max-width: 600px;
            margin: 50px auto;
            background-color: #d8d8d8;
            color: #20252b !important;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .button
        {
            background-color: {{ settings('color_1') }};
            color: #ffffff !important;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        hr
        {
            border-width: 0.1px;
        }

        a
        {
            color: {{ settings('color_1') }} !important;
        }
    </style>
</head>
<body>

<div class="container">
    @php
        $logoPath = Storage::disk('upload')->path(settings('logo'));
    @endphp
    <div style="text-align: center">
        <img src="{{ (settings('logo') && file_exists($logoPath)) ? $message->embed($logoPath) : Storage::disk('upload')->url(settings('logo')) }}" style="height: 50px;" alt="{{ settings('title') }}">
    </div>
    <p><strong>Merhaba,</strong></p>
    <p>Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:</p>

    <div style="text-align: center">
        <a href="{{ $actionUrl }}" class="button">Şifreyi Sıfırla</a>
    </div>
    <p>Bu işlemi siz yapmadıysanız, lütfen bu e-postayı dikkate almayın.</p>
    <p>Saygılarımızla,</p>
    <p>{{ settings('title') }}</p>
    <hr>
    <p>"Şifreyi Sıfırla" butonuna tıklamada sorun yaşıyorsanız, aşağıdaki URL'yi kopyalayıp web tarayıcınıza yapıştırın: <a href="{{ $actionUrl }}">{{ $actionUrl }}</a> </p>
</div>
</body>
</html>
