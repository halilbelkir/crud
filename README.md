# Zaurac CRUD Package

Laravel projelerinde hızlı ve kolay şekilde CRUD işlemleri geliştirmek için hazırlanmış bir pakettir.  
`Laravel 13` uyumludur. PHP 8.3+ gerektirir.

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

## 3️⃣ Public Asset'lerin Kopyalanması

Paket, `public` klasöründeki asset'leri (CSS, JS, görseller) projenizin `public/crud` dizinine **kopyalar** (symlink kullanılmaz; symlink production ortamlarında sorun çıkardığı için kopyalama tercih edilmiştir).

İlk istekte `public/crud` yoksa otomatik kopyalanır. Ayrıca paketin `composer install` / `composer update` adımlarında `crudPackage\CrudServiceProvider::publishAssets` script'i çalışarak asset'leri yeniden (force) kopyalar.

> **Önemli:** Composer yalnızca **root projenin** script'lerini çalıştırır; bir bağımlılık paketinin script'leri tüketici projede otomatik tetiklenmez. Bu nedenle paketi tüketen projede (ör. ana uygulamanız) `composer install/update` sonrası asset'lerin yenilenmesi için **ana projenin** `composer.json` dosyasına aşağıdaki script'leri ekleyin:

```json
"scripts": {
    "post-install-cmd": [
        "crudPackage\\CrudServiceProvider::publishAssets"
    ],
    "post-update-cmd": [
        "crudPackage\\CrudServiceProvider::publishAssets"
    ]
}
```

> `routes` ve `lang` dosyalarını kopyalamak isterseniz ayrıca şu komutu çalıştırabilirsiniz:

```bash
php artisan vendor:publish --provider="crudPackage\CrudServiceProvider" --tag=all
```

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

## 2️⃣ cache.php Düzenlemesi (Laravel 13)

Laravel 13'te cache'te saklanan PHP nesneleri için izin listesi gereklidir. Ana projenizin `config/cache.php` dosyasındaki `serializable_classes` ayarını aşağıdaki gibi güncelleyin:

```php
'serializable_classes' => [
    crudPackage\Models\Setting::class,
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