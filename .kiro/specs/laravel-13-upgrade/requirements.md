# Gereksinimler Dokümanı

## Giriş

Bu doküman, `zaurac/crud` Laravel paketinin Laravel 12'den Laravel 13'e yükseltilmesi için gerekli tüm gereksinimleri tanımlar. Paket, Laravel projelerinde hızlı CRUD işlemleri geliştirmek için kullanılmaktadır. Yükseltme; bağımlılık güncellemeleri, deprecated API'lerin değiştirilmesi, hatalı import'ların düzeltilmesi ve kod kalitesi iyileştirmelerini kapsar.

## Sözlük

- **Paket**: `zaurac/crud` Laravel CRUD paketi
- **Composer_Manifest**: Paketin `composer.json` dosyası; bağımlılıkları ve meta verileri tanımlar
- **Facade_Alias**: Laravel'in `Session`, `Validator` gibi kısa sınıf adları; Laravel 13'te kaldırılma riski taşır
- **Fully_Qualified_Facade**: `Illuminate\Support\Facades\Session` gibi tam namespace yolu ile kullanılan Facade sınıfı
- **Service_Provider**: `CrudServiceProvider`; paketin Laravel'e kayıt ve başlatma işlemlerini yöneten sınıf
- **Controller**: HTTP isteklerini işleyen sınıflar (`AuthController`, `CrudController`, `ModuleController` vb.)
- **Middleware**: HTTP isteklerini filtreleyen sınıflar (`CheckPermission`, `Variables`)
- **Exception_Class**: Hata yakalama için kullanılan PHP sınıfı
- **ImageUpload_Library**: `Intervention/Image` kütüphanesini kullanan resim yükleme sınıfı
- **README**: Paketin kurulum ve kullanım talimatlarını içeren `README.md` dosyası

## Gereksinimler

### Gereksinim 1: Composer Bağımlılık Güncellemesi

**Kullanıcı Hikayesi:** Bir geliştirici olarak, paketin Laravel 13 uyumlu bağımlılıklara sahip olmasını istiyorum, böylece Laravel 13 projelerinde sorunsuz kurulum yapabileyim.

#### Kabul Kriterleri

1. THE Composer_Manifest SHALL require `php` sürümünü `^8.3` olarak belirtmeli
2. THE Composer_Manifest SHALL require `illuminate/support` sürümünü `^12.0|^13.0` olarak belirtmeli
3. THE Composer_Manifest SHALL `yajra/laravel-datatables-oracle` bağımlılığını wildcard (`*`) yerine Laravel 13 uyumlu bir sürüm kısıtlaması (`^11.0|^12.0`) ile belirtmeli
4. THE Composer_Manifest SHALL `intervention/image` bağımlılığını wildcard (`*`) yerine uyumlu bir sürüm kısıtlaması (`^3.0`) ile belirtmeli
5. THE Composer_Manifest SHALL `spatie/laravel-activitylog` bağımlılığını wildcard (`*`) yerine uyumlu bir sürüm kısıtlaması (`^4.0`) ile belirtmeli

### Gereksinim 2: Facade Alias Kullanımlarının Düzeltilmesi

**Kullanıcı Hikayesi:** Bir geliştirici olarak, tüm controller dosyalarında global Facade alias'ları yerine fully qualified Facade import'larının kullanılmasını istiyorum, böylece Laravel 13'te deprecated alias'lardan kaynaklanan hatalar oluşmasın.

#### Kabul Kriterleri

1. WHEN bir Controller dosyasında `use Session;` ifadesi bulunduğunda, THE Paket SHALL bu ifadeyi `use Illuminate\Support\Facades\Session;` ile değiştirmeli
2. WHEN bir Controller dosyasında `use Validator;` ifadesi bulunduğunda, THE Paket SHALL bu ifadeyi `use Illuminate\Support\Facades\Validator;` ile değiştirmeli
3. THE Paket SHALL `AuthController`, `CrudController`, `ModuleController`, `MenuController`, `RoleGroupController`, `UserController`, `MainController`, `SettingController` ve `MediaController` dosyalarındaki tüm Facade_Alias kullanımlarını Fully_Qualified_Facade ile değiştirmeli

### Gereksinim 3: Hatalı Exception Sınıfı Kullanımlarının Düzeltilmesi

**Kullanıcı Hikayesi:** Bir geliştirici olarak, controller dosyalarında `Mockery\Exception` yerine doğru PHP Exception sınıfının kullanılmasını istiyorum, böylece hata yakalama mekanizması doğru çalışsın.

#### Kabul Kriterleri

1. WHEN bir Controller dosyasında `use Mockery\Exception;` ifadesi bulunduğunda, THE Paket SHALL bu ifadeyi kaldırmalı ve catch bloklarında `\Exception` kullanmalı
2. THE Paket SHALL `CrudController`, `ModuleController`, `MenuController`, `RoleGroupController`, `UserController` ve `MainController` dosyalarındaki tüm `Mockery\Exception` referanslarını düzeltmeli
3. WHEN bir catch bloğunda `catch (Exception $e)` ifadesi `Mockery\Exception`'a referans verdiğinde, THE Paket SHALL bunu `catch (\Exception $e)` olarak değiştirmeli

### Gereksinim 4: Service Provider Güvenliği

**Kullanıcı Hikayesi:** Bir geliştirici olarak, `CrudServiceProvider`'ın `boot()` metodu içinde otomatik migration çalıştırmamasını istiyorum, böylece paket kurulumunda beklenmeyen veritabanı değişiklikleri oluşmasın.

#### Kabul Kriterleri

1. THE Service_Provider SHALL `boot()` metodu içinde `Artisan::call('migrate')` komutunu çalıştırmamalı; bunun yerine migration'ları yalnızca `loadMigrationsFrom()` ile kaydetmeli
2. THE Service_Provider SHALL `runMigrations()` metodunu kaldırmalı veya devre dışı bırakmalı

### Gereksinim 5: Üretim Kodunda Debug İfadelerinin Kaldırılması

**Kullanıcı Hikayesi:** Bir geliştirici olarak, üretim kodunda `dd()` çağrılarının bulunmamasını istiyorum, böylece canlı ortamda beklenmeyen çıktılar oluşmasın.

#### Kabul Kriterleri

1. THE Paket SHALL `AuthController` dosyasındaki catch blokları içindeki `dd($e)` çağrılarını kaldırmalı
2. THE Paket SHALL `ModuleController` dosyasındaki `dd()` çağrılarını uygun loglama mekanizması ile değiştirmeli veya kaldırmalı
3. IF bir hata oluştuğunda, THE Controller SHALL hatayı `dd()` ile göstermek yerine JSON hata yanıtı döndürmeli


### Gereksinim 7: User Model PHPDoc Düzeltmesi

**Kullanıcı Hikayesi:** Bir geliştirici olarak, `User` modelindeki PHPDoc annotation'larının Laravel 13 Pint kurallarına uygun olmasını istiyorum, böylece statik analiz araçları doğru çalışsın.

#### Kabul Kriterleri

1. THE Paket SHALL `User.php` dosyasındaki `/** @use HasFactory<\Database\Factories\UserFactory> */` annotation'ını paketin kendi namespace'ine uygun şekilde düzeltmeli veya generic factory referansı kullanmalı

### Gereksinim 8: Middleware Uyumluluğu

**Kullanıcı Hikayesi:** Bir geliştirici olarak, middleware sınıflarının Laravel 13 ile uyumlu `handle` metodu imzalarına sahip olmasını istiyorum, böylece request işleme zinciri sorunsuz çalışsın.

#### Kabul Kriterleri

1. THE Middleware SHALL `handle(Request $request, Closure $next): Response` imzasını korumalı; bu imza Laravel 13 ile uyumludur
2. WHEN Laravel 13'te CSRF middleware adı `PreventRequestForgery` olarak değiştiğinde, THE Paket SHALL kendi route tanımlarında veya middleware kayıtlarında bu değişikliği dikkate almalı

### Gereksinim 9: README Güncellenmesi

**Kullanıcı Hikayesi:** Bir geliştirici olarak, README dosyasının Laravel 13 uyumluluğunu yansıtmasını istiyorum, böylece paket kullanıcıları doğru bilgiye sahip olsun.

#### Kabul Kriterleri

1. THE README SHALL "Laravel 12 uyumludur" ifadesini "Laravel 13 uyumludur" olarak güncellemeli
2. THE README SHALL minimum PHP sürüm gereksinimini (PHP 8.3+) belirtmeli

### Gereksinim 10: Intervention Image Uyumluluğu

**Kullanıcı Hikayesi:** Bir geliştirici olarak, `ImageUpload` sınıfının Intervention Image v3 API'sini Laravel 13 ortamında doğru kullanmasını istiyorum, böylece resim yükleme işlemleri sorunsuz çalışsın.

#### Kabul Kriterleri

1. THE ImageUpload_Library SHALL Intervention Image v3 Imagick Driver kullanımını korumalı; bu API Laravel 13 ile uyumludur
2. THE ImageUpload_Library SHALL `ImageManager`, `Driver` ve `Image` sınıflarının import yollarının Intervention Image v3 ile uyumlu olduğunu doğrulamalı
