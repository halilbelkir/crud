<?php

namespace crudPackage\Library\Routes;

use crudPackage\Http\Controllers\ModuleController;
use crudPackage\Models\Crud;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CrudRoutes
{
    public static function getRoutes()
    {
        $cruds = Crud::where('main',0)->get();

        foreach ($cruds as $crud)
        {
            $route = $crud->slug;

            Route::delete($route .'/fileDestroy/{id}/{order}/{column_name}', [ModuleController::class, 'fileDestroy'])->name($route .'.fileDestroy');
            Route::post($route .'/realtime/{'.Str::singular($crud->table_name).'}', [ModuleController::class, 'realtime'])->name($route .'.realtime')->middleware('checkPermission');
            Route::post($route .'/orderable', [ModuleController::class, 'orderable'])->name($route .'.orderable');
            Route::get($route .'/datatable', [ModuleController::class, 'datatable'])->name($route .'.datatables');
            Route::get($route .'/copy/{'.Str::singular($crud->table_name).'}', [ModuleController::class, 'copy'])->name($route .'.copy')->middleware('checkPermission');
            Route::resource($route, ModuleController::class)->middleware('checkPermission');
        }
    }

    public static function routes()
    {
        Route::get('/', function () {return view('crudPackage::auth.login');})->name('login');
        Route::post('loginStore', [\crudPackage\Http\Controllers\AuthController::class,'index'])->name('auth.login.store');
        Route::get('/forgot-password', function () {return view('crudPackage::auth.forgot');})->name('auth.password.forgot');
        Route::get('/reset-password', function () {return view('crudPackage::auth.reset');})->name('password.reset');
        Route::post('/forgot-send', [\crudPackage\Http\Controllers\AuthController::class,'forgotSend'])->name('auth.password.forgot.send');
        Route::post('/reset-send', [\crudPackage\Http\Controllers\AuthController::class,'passwordResetUpdate'])->name('auth.password.reset.update');
        Route::get('cikis-yap', [\crudPackage\Http\Controllers\AuthController::class,'logout'])->name('logout');

        Route::fallback(function () {return abort(404);})->middleware('variables');

        Route::middleware(['auth','variables'])->group(function ()
        {
            Route::get('/dashboard', function () {return view('crudPackage::dashboard');})->name('dashboard');
            Route::post('status-update', [\crudPackage\Http\Controllers\MainController::class, 'statusUpdate'])->name('statusUpdate');
            Route::post('single/crud/{crud?}', [\crudPackage\Http\Controllers\MainController::class, 'crud'])->name('single.crud');
            Route::post('ckeditor/image-upload', [\crudPackage\Http\Controllers\CkeditorImageUploadController::class, 'storeImage'])->name('ckeditor.imageUpload');

            Route::post('menus/orderable', [\crudPackage\Http\Controllers\MenuController::class, 'orderable'])->name('menus.orderable');
            Route::get('menus/datatable', [\crudPackage\Http\Controllers\MenuController::class, 'datatable'])->name('menus.datatables');
            Route::resource('menus', \crudPackage\Http\Controllers\MenuController::class)->middleware('checkPermission');

            Route::get('users/datatable', [\crudPackage\Http\Controllers\UserController::class, 'datatable'])->name('users.datatables');
            Route::resource('users', \crudPackage\Http\Controllers\UserController::class)->middleware('checkPermission');

            Route::get('role-groups/datatable', [\crudPackage\Http\Controllers\RoleGroupController::class, 'datatable'])->name('role-groups.datatables');
            Route::resource('role-groups', \crudPackage\Http\Controllers\RoleGroupController::class);

            Route::put('cruds/repeater/store/{crud}', [\crudPackage\Http\Controllers\CrudController::class, 'repeaterStore'])->name('cruds.repeater.store');
            Route::delete('cruds/repeater/delete/{crud_item}', [\crudPackage\Http\Controllers\CrudController::class, 'repeaterDestroy'])->name('cruds.repeater.destroy');
            Route::delete('cruds/relationship/delete/{crud_item}', [\crudPackage\Http\Controllers\CrudController::class, 'relationshipDestroy'])->name('cruds.relationship.destroy');
            Route::put('cruds/relationship/store/{crud}', [\crudPackage\Http\Controllers\CrudController::class, 'relationshipStore'])->name('cruds.relationship.store');
            Route::post('cruds/orderable', [\crudPackage\Http\Controllers\CrudController::class, 'orderable'])->name('cruds.orderable');
            Route::post('cruds/getColumns', [\crudPackage\Http\Controllers\CrudController::class, 'getColumns'])->name('cruds.create.columns');
            Route::get('cruds/create/{table_name}', [\crudPackage\Http\Controllers\CrudController::class, 'create'])->name('cruds.create.new');
            Route::get('cruds/datatable', [\crudPackage\Http\Controllers\CrudController::class, 'datatable'])->name('cruds.datatables');
            Route::resource('cruds', \crudPackage\Http\Controllers\CrudController::class);

            self::getRoutes();
        });
    }
}