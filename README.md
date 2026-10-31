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

### Google Fonts API Key (Panel Font Seçimi)

Ayarlar sayfasındaki font seçiminin tüm Google Fonts listesiyle (1900+ font) çalışabilmesi için `.env` dosyasına API key eklenmelidir:

```bash
GOOGLE_FONTS_API_KEY=your-api-key
```

API key almak için:

1. [Google Cloud Console](https://console.cloud.google.com) adresine Google hesabınızla girin.
2. Üstten bir proje seçin veya **New Project** ile yeni proje oluşturun.
3. [Web Fonts Developer API](https://console.cloud.google.com/apis/library/webfonts.googleapis.com) sayfasına gidip **Enable** butonuna tıklayın.
4. Sol menüden **APIs & Services → Credentials → + Create Credentials → API key** adımlarını izleyin.
5. Oluşan anahtarı kopyalayıp `.env` dosyasına ekleyin. (Güvenlik için anahtara "Web Fonts Developer API" kısıtlaması eklemeniz önerilir.)

> API key tanımlı değilse font alanı serbest metin girişine düşer; font listesi 1 hafta önbelleklenir. Font seçilmezse panelin varsayılan fontu kullanılır. Seçilen font panele, giriş ekranına ve maillere uygulanır (Gmail/Outlook gibi bazı mail istemcileri webfont yüklemediği için maillerde Arial'a düşebilir).

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
                'root'   => env('UPLOAD_PATH') ?: public_path() . '/upload',
                'url'    => env('UPLOAD_URL') ?: '/upload',
            ],
```

Panel + front ayrı uygulamalarsa yüklemelerin fiziksel olarak front projesinde tutulması için `.env` dosyasına front yolunu ekleyin (symlink kullanılmaz):

```bash
UPLOAD_PATH=/path/to/front/public/upload
UPLOAD_URL=https://front-domain.com/upload
```

Env tanımlanmazsa veya boş bırakılırsa yüklemeler panelin kendi `public/upload` klasörüne yapılır. Tanımda `env('UPLOAD_PATH', ...)` yerine `?:` kullanılmasının sebebi budur: Laravel'de `.env` içinde tanımlı ama boş bırakılan bir değişken için `env()` varsayılanı değil boş metin döndürür; bu durumda disk kökü boş kalır ve yüklemeler yanlış dizine gider.

---

## 2️⃣ cache.php Düzenlemesi (Laravel 13)

Laravel 13'te cache'te saklanan PHP nesneleri için izin listesi gereklidir. Ana projenizin `config/cache.php` dosyasındaki `serializable_classes` ayarını aşağıdaki gibi güncelleyin:

```php
'serializable_classes' => [
    crudPackage\Models\Setting::class,
],
```

---

## 🎨 Temalar ve Bileşen Sözleşmesi

Panel teması **Ayarlar** sayfasından seçilir. Temalar paketin `resources/themes/{tema}` klasöründe durur; her temanın `theme.json` dosyasında başlığı ve hazır olup olmadığı yazar:

```json
{"title" : "Varsayılan", "ready" : true}
```

Yalnızca `ready` değeri `true` olan temalar seçilebilir. Aktif temada bulunmayan her view otomatik olarak `default` temadan gelir. Bu yüzden view yolları temadan bağımsızdır: `crudPackage::users.index` hangi tema seçili olursa olsun aynı isimle çağrılır.

### Önizleme görselleri

Ayarlar sayfasında her tema bir kart olarak listelenir. Kartta temanın ilk görseli kapak olarak görünür, **Önizle** butonu bütün görselleri galeride açar. Görseller paketin `public/themes/{tema}/previews` klasörüne konur; klasördeki `png`, `jpg`, `jpeg` ve `webp` dosyaları otomatik bulunur, ayrıca bir yere yazmak gerekmez. Sıra dosya adına göre belirlenir:

```
public/themes/default/previews/1-giris.jpg
public/themes/default/previews/2-liste.jpg
public/themes/default/previews/3-form.jpg
```

Görseller diğer asset'lerle birlikte projenin `public/crud/themes` klasörüne kopyalanır. Yeni eklenen görsellerin projede görünmesi için `publishAssets` script'inin çalışması (composer install/update) gerekir.

### Tema CSS ve JS derlemesi

`panel` teması Tailwind v4, Alpine ve CKEditor 5 ile paketin içinde derlenir. Derlenmiş dosyalar (`public/themes/panel/app.css`, `app.js` ve `chunks/`) pakete commit'lenir, projelerde Node kurmak gerekmez.

```bash
npm ci
npm run build
```

Kaynaklar `resources/themes/panel/assets` altındadır; derleme `resources/themes/panel/views` altındaki Blade dosyalarını tarar. Yalnızca editör kullanılan sayfalarda CKEditor parçası (`chunks/ckeditor5-*.js`) yüklenir.

### Özel sayfalar

Projede yazılan özel sayfalar yolları değiştirmeden aktif temanın görünümünü alır. Bunun için sayfa paketin layout'unu genişletir ve HTML yerine paket bileşenlerini kullanır:

```blade
@extends('crudPackage::layout.main', ['activePage' => 'Raporlar', 'parentPage' => 'Yönetim', 'parentPageRoute' => route('dashboard')])

@section('content')
    <x-crudPackage::button href="{{ route('reports.create') }}">Yeni Rapor</x-crudPackage::button>
    <x-crudPackage::badge tone="success">Aktif</x-crudPackage::badge>
@endsection
```

Layout'un sözleşmesi: `content`, `style`, `js` bölümleri, datatable sayfaları için `datatables.ajax.url`, `datatables.columns`, `datatables.files.title`, `datatables.files.columns` bölümleri ve `activePage`, `parentPage`, `parentPageRoute` parametreleri. Her tema bu isimleri aynen karşılar.

### Bileşenler

| Bileşen | Parametreler | Açıklama |
|---|---|---|
| `button` | `variant` (primary), `size`, `href`, `type` (button), `class` | `href` verilirse link, verilmezse buton olarak çizilir |
| `badge` | `tone` (primary), `size`, `class` | Durum ve etiket rozeti |
| `icon` | `name`, `class` | `move`, `down`, `trash`, `bookmark` ortak isimleri temaya göre çevrilir; diğer isimler temanın ikon setinden gelir |
| `action-menu` | `id`, `items`, `label` (Aksiyon) | Datatable satır aksiyonları; öğe tipleri aşağıda |
| `field` | `label`, `for`, `required`, `info`, `hidden`, `class` (col-12), `labelClass` | Form alanı sarmalayıcısı; alanın kendisi slot olarak verilir |
| `repeater-item` | `no` | Repeater satırı; sürükleme tutamağı ve silme bağlantısıyla gelir |
| `menu-item` | `item` | Sol menü öğesi; `menuGenerate()` tarafından kullanılır |
| `loading` | — | Yükleniyor göstergesi |
| `card` | `title`, `class`, `headerClass`, `bodyClass` | Kart; `header` ve `toolbar` slotları alır |
| `alert` | `tone` (primary), `icon`, `title`, `class` | Bilgilendirme kutusu |
| `modal` | `id`, `title`, `size`, `headerId` | Modal; vazgeç butonu `data-dismiss="modal"` ile çalışır |
| `datatable` | `columns`, `hover` (true), `class` | Arama, dışa aktar menüsü ve tablo; `filters`, `toolbar`, `head` slotları alır |
| `input` | `type` (text), `class` | Metin alanı |
| `select` | `class` | Seçim kutusu; seçenekler slot olarak verilir |
| `textarea` | `class` | Çok satırlı metin alanı |
| `checkbox` | `label`, `id`, `class`, `wrapperClass` (mb-3) | Onay kutusu |
| `file` | `class` | Dosya seçme alanı |
| `image-input` | `name`, `value`, `src`, `title`, `placeholder` | Görsel yükleme alanı; `value` doluysa mevcut görseli önizlemeyle gösterir (`.showImage` / `.imageUpdate` kancaları) |
| `password` | `name`, `placeholder`, `autocomplete` (new-password) | Göster/gizle düğmeli şifre alanı |
| `form` | `action`, `method`, `id` (addUpdateForm), `class` | Kart içinde ajax formu; `method` verilirse `@method` eklenir |
| `submit` | `label` (Kaydet) | Kaydet butonu (`.buttonForm`) ve yükleniyor göstergesi |

Bileşenlere verilen diğer bütün nitelikler (`name`, `placeholder`, `data-*`, `onchange` vb.) olduğu gibi asıl elemana geçer.

`</x-slot:...>` kapanışından sonra yeni satıra geçin. Kapanışla aynı satırda hemen arkasından metin gelirse Blade slotu kapatamaz.

Liste sayfası örneği:

```blade
<x-crudPackage::datatable :columns="['Başlık', 'Durum', '']">
    <x-slot:toolbar>
        <x-crudPackage::button data-bs-toggle="modal" data-bs-target="#insertModal">
            <x-crudPackage::icon name="plus" class="fs-2" /> Ekle
        </x-crudPackage::button>
    </x-slot:toolbar>
</x-crudPackage::datatable>

<x-crudPackage::modal id="insertModal" title="Rapor Ekle" size="xl">
    <form id="addUpdateForm" method="post" action="{{ route('reports.store') }}">
        <x-crudPackage::field label="Başlık" required>
            <x-crudPackage::input name="title" placeholder="Başlık" />
        </x-crudPackage::field>
    </form>
</x-crudPackage::modal>
```

### JavaScript kancaları

Panelin JavaScript katmanı bütün temalarda ortaktır ve aşağıdaki işaretlere bağlanır. Bileşenler bunları kendisi üretir; yeni bir temanın bileşenleri de aynı işaretleri korumak zorundadır:

| İşaret | Görevi |
|---|---|
| `#data-tables` | Datatable'ın kurulduğu tablo |
| `[data-kt-data-table-filter="search"]` | Datatable arama kutusu |
| `#exportButtons [data-table-export-button-name]` | Excel / Pdf dışa aktarma |
| `#addUpdateForm` | Ajax ile gönderilen form |
| `div.form-group` | Doğrulama hatalarının yazıldığı alan sarmalayıcısı |
| `#insertModal`, `#fileModal`, `#repeaterModal` ve `[data-dismiss="modal"]` | Vazgeç onaylı modal davranışı |
| `.buttonForm`, `.loading` | Kaydet butonu ve yükleniyor göstergesi |
| `.showImage`, `.imageUpdate`, `.imageUpdatePreview` | Görsel seçildiğinde önizlemenin güncellenmesi |

`action-menu` öğeleri:

```php
['label' => 'Detay', 'href' => route('reports.show', $id)]
['type' => 'status', 'model' => 'Report', 'id' => $id, 'status' => $status]
['type' => 'destroy', 'title' => 'Bu raporu', 'route' => route('reports.destroy', $id)]
```

### Controller'dan HTML üretmek

Datatable kolonları gibi controller'da HTML üretilen yerlerde de HTML elle yazılmaz; aynı bileşenler `crudComponent()` ile çağrılır, böylece çıktı aktif temaya göre değişir:

```php
->editColumn('status', fn ($value) => crudStatusBadge($value->status))
->addColumn('actions', fn ($value) => crudComponent('action-menu',
    [
        'id'    => $value->id,
        'items' => [['label' => 'Detay', 'href' => route('reports.show', $value->id)]],
    ]
))
```

`crudComponent($name, $parameters, $slot)` bileşeni render edip HTML döndürür. `crudStatusBadge($status, $on, $off, $size)` ise 1 için yeşil (varsayılan "Aktif"), diğer değerler için kırmızı (varsayılan "Pasif") rozet üretir.

Yeni bir tema eklenirken bu tablodaki bütün bileşenler o temanın `views/components` klasöründe aynı isim ve parametrelerle yazılır. Eksik kalan bileşen `default` temadan gelir.

### E-posta görünümü

Panelden gönderilen bütün mailler (kullanıcı daveti, şifre sıfırlama) tek bir iskeletten çıkar. Metinler **E-posta Şablonları** sayfasından, logo, ana renk (Panel Renk 1) ve panel adı **Ayarlar** sayfasından değişir. Yerleşim, yazı boyutları ve sabit renkler gibi görünüm değişiklikleri ise proje bazında view override ile yapılır; paket koduna dokunulmaz.

| Paketteki dosya | Projedeki karşılığı | Ne değişir |
|---|---|---|
| `resources/themes/default/views/components/emails/layout.blade.php` | `resources/views/vendor/crudPackage/components/emails/layout.blade.php` | Üst bant, logo, kart, alt bilgi; bütün maillerin iskeleti |
| `resources/themes/default/views/emails/template.blade.php` | `resources/views/vendor/crudPackage/emails/template.blade.php` | Başlık, metin, buton, not kutusu ve yedek bağlantı satırı |

Değiştirilmek istenen dosya projeye kopyalanır ve orada düzenlenir:

```bash
mkdir -p resources/views/vendor/crudPackage/components/emails
cp vendor/zaurac/crud/resources/themes/default/views/components/emails/layout.blade.php resources/views/vendor/crudPackage/components/emails/
php artisan view:clear
```

Laravel `resources/views/vendor/crudPackage` klasörünü paketin view'larından önce okur; projede bulunan dosya kullanılır, bulunmayan paketten gelir. Yalnızca değiştirilecek dosya kopyalanır, böylece paket güncellemeleri diğer dosyalara gelmeye devam eder.

Override dosyasında uyulacak sözleşme:

- `layout` bileşeni `title`, `preview` ve `mail` parametrelerini alır, içerik `$slot` olarak gelir. `mail` gönderim sırasında mesaj nesnesidir; logo bununla gömülür (`$mail->embedData(...)`), önizlemede boştur.
- `template` view'ı `$template` dizisini (`subject`, `heading`, `content`, `button`, `note`, `preview`) ve `$url` değişkenini alır. `content` ve `note` editörden HTML olarak gelir, `{!! !!}` ile basılır.
- Renk, font ve ad için `crudBrand('color.primary')`, `crudBrand('font.body')`, `crudBrand('name')` kullanılır; böylece Ayarlar'daki değişiklikler override edilen mailde de geçerli kalır.
- Mail istemcileri harici CSS ve çoğu web fontunu yüklemez; stiller satır içi (`style="..."`) yazılır, yerleşim tablo ile kurulur.

Sonuç **E-posta Şablonları → Önizle** ile panelde görülür.

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