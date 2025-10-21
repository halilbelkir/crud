# Zaurac CRUD Package

Laravel projelerinde hızlı ve kolay şekilde CRUD işlemleri geliştirmek için hazırlanmış bir pakettir.  
`Laravel 12` uyumludur.

---

## 1️⃣ composer.json Repository Alanı

Ana projenizin `composer.json` dosyasına aşağıdaki gibi **repositories** alanını ekleyin:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/halilbelkir/crud.git"
    }
]
```

---

## 2️⃣ Composer ile Paketi Yükleme

```bash
composer require zaurac/crud:dev-main
```

> Eğer farklı bir branch kullanıyorsanız, `dev-branch-adi` şeklinde belirtebilirsiniz.

---

## 3️⃣ Publish İşlemleri

Paketi yükledikten sonra gerekli publish komutlarını çalıştırın:

```bash
php artisan vendor:publish --provider="crudPackage\CrudServiceProvider" --tag=all
```

Bu işlem ile:
- `routes`
- `views`
- `public` asset’ler  
projeye kopyalanır.

---

## 4️⃣ .env Yapılandırması

Repoda gelen `.env.example` dosyasını kendi projenize kopyalayın:

```bash
cp vendor/zaurac/crud/.env.example .env
```

Önyüzde cache yapısı kullanıldığında, .env dosyasına aşağıdaki parametre eklenmelidir. Bu parametre, önyüzde oluşturacağınız cache:clear fonksiyonunun bağlantı (URL) adresini belirtmelidir.
Bu işlem; ekleme, düzenleme, silme ve kopyalama gibi durumlarda otomatik olarak çalışacaktır.

```bash
APP_CACHE_URL=https://example.com/cache-clear
```

Daha sonra `.env` dosyasını ihtiyacınıza göre düzenleyin.

---

## 5️⃣ Migration Çalıştırma

Veritabanı tablolarını oluşturmak için:

```bash
php artisan migrate
```

## 1️⃣ filesystem.php Düzenelemesi

Ana projenizin `config/filesystem.php` dosyasına aşağıdaki gibi **upload** alanını ekleyin:

```array
'upload' =>
            [
                'driver' => 'local',
                'root'   => public_path() . '/upload',
                'url'    => '/upload',
            ],
```

---

✅ Artık paketiniz kullanıma hazırdır!

### Kullanıcı Bilgileri

Kullanıcı Adı: 

```bash
admin@zaurac.io
```

Şifre : 

```bash
Zaurac12345.,
```