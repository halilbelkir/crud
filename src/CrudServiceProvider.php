<?php

namespace crudPackage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Model;
use crudPackage\Listeners\GlobalCrudListener;
use Illuminate\Support\Facades\Event;


class CrudServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Event::listen('eloquent.created: *', function (string $event, array $data) {
            app(GlobalCrudListener::class)->created($data[0]);
        });

        Event::listen('eloquent.updated: *', function (string $event, array $data) {
            app(GlobalCrudListener::class)->updated($data[0]);
        });

        Event::listen('eloquent.deleted: *', function (string $event, array $data) {
            app(GlobalCrudListener::class)->deleted($data[0]);
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->runMigrations();

        $packagePublic = __DIR__ . '/../public';
        $laravelPublic = public_path('crud');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'crudPackage');
        $this->loadHelpers();


        if (!file_exists($laravelPublic))
        {
            mkdir($laravelPublic, 0777, true);
        }

        $this->removeDirectory($laravelPublic);

        if (!is_link($laravelPublic)) {
            File::link($packagePublic, $laravelPublic);
        }

        $this->replaceWebRoutes();

        config(['auth.providers.users.model' => \crudPackage\Models\User::class]);

        $this->app['router']->aliasMiddleware('variables', \crudPackage\Http\Middleware\Variables::class);
        $this->app['router']->aliasMiddleware('checkPermission', \crudPackage\Http\Middleware\CheckPermission::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../routes/web.php' => base_path('routes/web.php'),
                __DIR__.'/../resources/lang' => base_path('resources/lang'),
            ], 'all');
        }
    }

    protected function runMigrations()
    {
        Artisan::call('migrate', [
            '--force' => true //
        ]);
    }


    public function register()
    {
        if (class_exists('Yajra\DataTables\DataTablesServiceProvider'))
        {
            $this->app->register('Yajra\DataTables\DataTablesServiceProvider');
        }
    }

    protected function loadHelpers()
    {
        $helperFile = __DIR__.'/Helpers/helpers.php';

        if (file_exists($helperFile)) {
            require $helperFile;
        }
    }
    protected function replaceWebRoutes()
    {
        $webRouteFile = base_path('routes/web.php');

        if (File::exists($webRouteFile))
        {
            File::delete($webRouteFile);
        }

        $packageRoutes = __DIR__ . '/../routes/web.php';

        // Kopyalama işlemi
        File::copy($packageRoutes, $webRouteFile);
    }

    protected function removeDirectory($directory)
    {
        if (!file_exists($directory)) return true;

        if (is_link($directory))
        {
            return unlink($directory);
        }

        if (is_dir($directory))
        {
            return rmdir($directory);
        }

        return unlink($directory);
    }
}