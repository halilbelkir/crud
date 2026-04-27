# Uygulama Planı: Laravel 13 Yükseltme

## Genel Bakış

Bu plan, `zaurac/crud` Laravel CRUD paketinin Laravel 13 ile uyumlu hale getirilmesi için gereken tüm kod değişikliklerini adım adım tanımlar. Değişiklikler dosya bazında `strReplace` ile yapılacak, hiçbir dosya sıfırdan yazılmayacaktır.

## Görevler

- [x] 1. Composer bağımlılık güncellemesi
  - [x] 1.1 `composer.json` dosyasına `php` ve `illuminate/support` gereksinimlerini ekle
    - `"require"` bloğuna `"php": "^8.3"` ve `"illuminate/support": "^12.0|^13.0"` ekle
    - _Gereksinimler: 1.1, 1.2_
  - [x] 1.2 Wildcard bağımlılıkları sabit sürümlere çevir
    - `"yajra/laravel-datatables-oracle": "*"` → `"^11.0|^12.0"`
    - `"intervention/image": "*"` → `"^3.0"`
    - `"spatie/laravel-activitylog": "*"` → `"^4.0"`
    - _Gereksinimler: 1.3, 1.4, 1.5_

- [x] 2. Facade alias düzeltmeleri (Session ve Validator)
  - [x] 2.1 `AuthController.php` — `use Session;` → `use Illuminate\Support\Facades\Session;` ve `use Validator;` → `use Illuminate\Support\Facades\Validator;`
    - _Gereksinimler: 2.1, 2.2, 2.3_
  - [x] 2.2 `CrudController.php` — `use Session;` → `use Illuminate\Support\Facades\Session;` ve `use Validator;` → `use Illuminate\Support\Facades\Validator;`
    - _Gereksinimler: 2.1, 2.2, 2.3_
  - [x] 2.3 `ModuleController.php` — `use Session;` → `use Illuminate\Support\Facades\Session;` ve `use Validator;` → `use Illuminate\Support\Facades\Validator;`
    - _Gereksinimler: 2.1, 2.2, 2.3_
  - [x] 2.4 `MenuController.php` — `use Session;` → `use Illuminate\Support\Facades\Session;` ve `use Validator;` → `use Illuminate\Support\Facades\Validator;`
    - _Gereksinimler: 2.1, 2.2, 2.3_
  - [x] 2.5 `RoleGroupController.php` — `use Session;` → `use Illuminate\Support\Facades\Session;` ve `use Validator;` → `use Illuminate\Support\Facades\Validator;`
    - _Gereksinimler: 2.1, 2.2, 2.3_
  - [x] 2.6 `UserController.php` — `use Session;` → `use Illuminate\Support\Facades\Session;` ve `use Validator;` → `use Illuminate\Support\Facades\Validator;`
    - _Gereksinimler: 2.1, 2.2, 2.3_
  - [x] 2.7 `MainController.php` — `use Session;` → `use Illuminate\Support\Facades\Session;` ve `use Validator;` → `use Illuminate\Support\Facades\Validator;`
    - _Gereksinimler: 2.1, 2.2, 2.3_
  - [x] 2.8 `MediaController.php` — `use Validator;` → `use Illuminate\Support\Facades\Validator;`
    - _Gereksinimler: 2.2, 2.3_
  - [x] 2.9 `SettingController.php` — `use Validator;` → `use Illuminate\Support\Facades\Validator;`
    - _Gereksinimler: 2.2, 2.3_

- [x] 3. Exception sınıfı düzeltmeleri
  - [x] 3.1 `CrudController.php` — `use Mockery\Exception;` satırını kaldır, tüm `catch (Exception $e)` → `catch (\Exception $e)`
    - _Gereksinimler: 3.1, 3.2, 3.3_
  - [x] 3.2 `ModuleController.php` — `use Mockery\Exception;` satırını kaldır, tüm `catch (Exception $e)` → `catch (\Exception $e)`
    - _Gereksinimler: 3.1, 3.2, 3.3_
  - [x] 3.3 `MenuController.php` — `use Mockery\Exception;` satırını kaldır, tüm `catch (Exception $e)` → `catch (\Exception $e)`
    - _Gereksinimler: 3.1, 3.2, 3.3_
  - [x] 3.4 `RoleGroupController.php` — `use Mockery\Exception;` satırını kaldır, tüm `catch (Exception $e)` → `catch (\Exception $e)`
    - _Gereksinimler: 3.1, 3.2, 3.3_
  - [x] 3.5 `UserController.php` — `use Mockery\Exception;` satırını kaldır, tüm `catch (Exception $e)` → `catch (\Exception $e)`
    - _Gereksinimler: 3.1, 3.2, 3.3_
  - [x] 3.6 `MainController.php` — `use Mockery\Exception;` satırını kaldır, tüm `catch (Exception $e)` → `catch (\Exception $e)`
    - _Gereksinimler: 3.1, 3.2, 3.3_
  - [x] 3.7 `MediaController.php` — Tüm `catch (Exception $e)` → `catch (\Exception $e)` (import yok, tanımsız referans düzeltmesi)
    - _Gereksinimler: 3.1, 3.3_

- [x] 4. Kontrol noktası — Facade ve Exception düzeltmelerini doğrula
  - `grep -r "use Session;" src/` ile global alias kalmadığını doğrula
  - `grep -r "use Validator;" src/` ile global alias kalmadığını doğrula (Facades hariç)
  - `grep -r "Mockery\\Exception" src/` ile Mockery referansı kalmadığını doğrula
  - Tüm testlerin geçtiğinden emin ol, sorular varsa kullanıcıya sor.

- [x] 5. Debug ifadelerinin kaldırılması
  - [x] 5.1 `AuthController.php` — `index` metodundaki catch bloğundaki `dd($e);` satırını kaldır
    - _Gereksinimler: 5.1, 5.3_
  - [x] 5.2 `AuthController.php` — `forgotSend` metodundaki catch bloğundaki `dd($e);` satırını kaldır
    - _Gereksinimler: 5.1, 5.3_
  - [x] 5.3 `ModuleController.php` — `datatable` metodundaki `if (config('app.debug')) { dd(...) }` bloğunu kaldır
    - _Gereksinimler: 5.2, 5.3_
  - [x] 5.4 `ModuleController.php` — `realtime` metodundaki catch bloğundaki `dd($e);` satırını kaldır
    - _Gereksinimler: 5.2, 5.3_

- [x] 6. MediaController düzeltmesi ve User Model PHPDoc güncellemesi
  - [x] 6.1 `MediaController.php` — `use App\Http\Controllers\Controller;` → `use crudPackage\Http\Controllers\Controller;`
    - _Gereksinimler: Tasarım Bileşen 7_
  - [x] 6.2 `User.php` — `/** @use HasFactory<\Database\Factories\UserFactory> */` → `/** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */`
    - _Gereksinimler: 7.1_

- [x] 7. README güncellemesi
  - [x] 7.1 `README.md` — "Laravel 12" ifadesini "Laravel 13" olarak güncelle ve PHP 8.3+ gereksinimini ekle
    - _Gereksinimler: 9.1, 9.2_

- [x] 8. Son kontrol noktası — Tüm değişiklikleri doğrula
  - `composer validate` ile composer.json geçerliliğini doğrula
  - `grep -r "dd(" src/` ile dd() çağrısı kalmadığını doğrula
  - `grep -r "use Session;" src/` ile global alias kalmadığını doğrula
  - `grep -r "Mockery\\Exception" src/` ile Mockery referansı kalmadığını doğrula
  - `grep -r "App\\Http\\Controllers\\Controller" src/` ile yanlış Controller extend kalmadığını doğrula
  - Tüm testlerin geçtiğinden emin ol, sorular varsa kullanıcıya sor.

## Notlar

- Tüm değişiklikler `strReplace` ile yapılacak, `fsWrite` kullanılmayacak
- Hiçbir yorum satırı eklenmeyecek
- Migration dosyası oluşturulmayacak ve otomatik migrate edilmeyecek
- Middleware imzaları zaten Laravel 13 uyumlu, değişiklik gerektirmiyor (Gereksinim 8)
- Intervention Image API zaten v3 uyumlu, değişiklik gerektirmiyor (Gereksinim 10)
- env() → config() dönüşümü kapsam dışıdır
