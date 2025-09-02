<?php

namespace crudPackage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CrudServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $packagePublic = __DIR__ . '/../public';  // Paketin public klasörü
        $laravelPublic = public_path('crud');  // Laravel projesindeki public/vendor/crud yolu

        // Rotaları, migrationları ve view'ları dahil et
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'crudPackage');
        $this->loadHelpers();


        // Eğer public/vendor/crud dizini yoksa, oluştur
        if (!file_exists($laravelPublic))
        {
            mkdir($laravelPublic, 0777, true);  // Klasörü oluştur
        }

        $this->removeDirectory($laravelPublic);

        // Burada sadece public klasörünü symlink olarak oluşturacağız
        if (!is_link($laravelPublic)) {
            File::link($packagePublic, $laravelPublic);
        }

        // Ana projedeki routes/web.php dosyasını silip yerine paketin `web.php` dosyasını koy
        $this->replaceWebRoutes();

        config(['auth.providers.users.model' => \crudPackage\Models\User::class]);


        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../routes/web.php' => base_path('routes/web.php'),
                __DIR__.'/../resources/lang' => base_path('resources/lang'),
            ], 'all');
        }
    }

    public function register()
    {
        // DataTables service provider'ı kaydet
        if (class_exists('Yajra\DataTables\DataTablesServiceProvider'))
        {
            $this->app->register('Yajra\DataTables\DataTablesServiceProvider');
        }

        $this->app['router']->aliasMiddleware('variables', crudPackage\Http\Middleware\Variables::class);
        $this->app['router']->aliasMiddleware('checkPermission', crudPackage\Http\Middleware\CheckPermission::class);
    }

    protected function loadHelpers()
    {
        $helperFile = __DIR__.'/Helpers/helpers.php';

        if (file_exists($helperFile)) {
            require $helperFile;
        }
    }

    /**
     * Ana projedeki web.php dosyasını silip, paketteki web.php dosyasını kopyalar.
     */
    protected function replaceWebRoutes()
    {
        // Ana projedeki routes/web.php dosyasının tam yolu
        $webRouteFile = base_path('routes/web.php');

        // Eğer routes/web.php dosyası varsa, sil
        if (File::exists($webRouteFile)) {
            File::delete($webRouteFile);
        }

        // Şimdi, paketteki routes/web.php dosyasını ana projeye kopyala
        $packageRoutes = __DIR__ . '/../routes/web.php';

        // Kopyalama işlemi
        File::copy($packageRoutes, $webRouteFile);
    }

    /**
     * Bir dizini ve içeriğini silmek için yardımcı fonksiyon
     */
    protected function removeDirectory($directory)
    {
        if (!file_exists($directory)) return true;

        // Eğer bir symlink ise, sil
        if (is_link($directory))
        {
            return unlink($directory);
        }

        // Eğer bir dizinse, içeriğiyle birlikte sil
        if (is_dir($directory))
        {
            return rmdir($directory);
        }

        return unlink($directory);
    }
}
