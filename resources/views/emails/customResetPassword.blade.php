<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Zaurac Teknoloji - Şifre Sıfırlama</title>
    <style>
        body
        {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container
        {
            max-width: 600px;
            margin: 50px auto;
            background-color: #001244;
            color: #FFF3E6 !important;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .button
        {
            background-color: #FFF3E6;
            color: #262631 !important;
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
            color: #FFF3E6 !important;
        }
    </style>
</head>
<body>

<div class="container">
    <div style="text-align: center">
        <img src="{{asset('crud/images/logo-light.png')}}" style="height: 50px;" alt="">
    </div>
    <p><strong>Merhaba,</strong></p>
    <p>Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:</p>

    <div style="text-align: center">
        <a href="{{ $actionUrl }}" class="button">Şifreyi Sıfırla</a>
    </div>
    <p>Bu işlemi siz yapmadıysanız, lütfen bu e-postayı dikkate almayın.</p>
    <p>Saygılarımızla,</p>
    <p>Zaurac Teknoloji</p>
    <hr>
    <p>"Şifreyi Sıfırla" butonuna tıklamada sorun yaşıyorsanız, aşağıdaki URL'yi kopyalayıp web tarayıcınıza yapıştırın: <a href="{{ $actionUrl }}">{{ $actionUrl }}</a> </p>
</div>
</body>
</html>
