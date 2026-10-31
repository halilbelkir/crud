<?php

namespace crudPackage\Library\Routes;

use crudPackage\Http\Controllers\ModuleController;
use crudPackage\Models\Crud;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrudRoutes
{
    public static function getRoutes()
    {
        $cruds = Crud::where('main',0)->get();

        foreach ($cruds as $crud)
        {
            $route = $crud->slug;

            Route::delete($route .'/fileDestroy/{id}/{order}/{column_name}/{language_code?}/{language_order?}', [ModuleController::class, 'fileDestroy'])->name($route .'.fileDestroy');
            Route::post($route .'/realtime/{id}/{locale?}', [ModuleController::class, 'realtime'])->name($route .'.realtime');
            Route::get($route .'/language/{locale?}', [ModuleController::class, 'index'])->name($route .'.locale');
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
        Route::get('davet/{token}', [\crudPackage\Http\Controllers\InviteController::class, 'create'])->name('invite.create');
        Route::post('davet', [\crudPackage\Http\Controllers\InviteController::class, 'store'])->name('invite.store');
        Route::get('cikis-yap', [\crudPackage\Http\Controllers\AuthController::class,'logout'])->name('logout');

        Route::fallback(function () {

            throw new HttpResponseException(
                response()->view(
                    'crudPackage::errors.404',
                    [
                        'message' => 'Aradığınız sayfa bulunamadı.'
                    ],
                    404
                )
            );

        })->middleware('variables');

        Route::middleware(['auth','variables',\crudPackage\Http\Middleware\AjaxRedirect::class])->group(function ()
        {
            Route::get('/dashboard', function () {return view('crudPackage::dashboard');})->name('dashboard');
            Route::post('durum', [\crudPackage\Http\Controllers\StatusController::class, 'update'])->name('status.update');
            Route::post('siralama', [\crudPackage\Http\Controllers\ReorderController::class, 'update'])->name('reorder');
            Route::get('profilim', [\crudPackage\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('profilim', [\crudPackage\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
            Route::put('profilim/sifre', [\crudPackage\Http\Controllers\ProfileController::class, 'password'])->name('profile.password');
            Route::post('single/crud/{crud?}', [\crudPackage\Http\Controllers\MainController::class, 'crud'])->name('single.crud');
            Route::get('single/relation-options/{crud}/{column}', [\crudPackage\Http\Controllers\MainController::class, 'relationOptions'])->name('single.relationOptions');
            Route::post('ckeditor/image-upload', [\crudPackage\Http\Controllers\CkeditorImageUploadController::class, 'storeImage'])->name('ckeditor.imageUpload');

            Route::resource('menus', \crudPackage\Http\Controllers\MenuController::class)->middleware('checkPermission');

            Route::middleware('checkPermission')->controller(\crudPackage\Http\Controllers\MenuItemController::class)->group(function ()
            {
                Route::get('menus/{menu}/items/create', 'create')->name('menus.items.create');
                Route::post('menus/{menu}/items', 'store')->name('menus.items.store');
                Route::post('menus/{menu}/items/order', 'order')->name('menus.items.updateOrder');
                Route::get('menus/items/{item}/edit', 'edit')->name('menus.items.edit');
                Route::put('menus/items/{item}', 'update')->name('menus.items.update');
                Route::delete('menus/items/{item}', 'destroy')->name('menus.items.destroy');
            });

            Route::post('users/{user}/davet', [\crudPackage\Http\Controllers\UserController::class, 'invite'])->name('users.invite.update')->middleware('checkPermission');
            Route::resource('users', \crudPackage\Http\Controllers\UserController::class)->middleware('checkPermission');

            Route::resource('role-groups', \crudPackage\Http\Controllers\RoleGroupController::class)->middleware('checkPermission');

            Route::resource('settings', \crudPackage\Http\Controllers\SettingController::class)->only(['index', 'update'])->middleware('checkPermission');

            Route::resource('mail-templates', \crudPackage\Http\Controllers\MailTemplateController::class)->only(['index', 'show', 'edit', 'update', 'destroy'])->parameters(['mail-templates' => 'key'])->middleware('checkPermission');

            Route::resource('logs', \crudPackage\Http\Controllers\LogController::class)->only(['index', 'show'])->middleware('checkPermission');

            Route::get('media/download/{path?}', [\crudPackage\Http\Controllers\MediaController::class, 'download'])->where('path', '.*')->name('media.download')->middleware('checkPermission');
            Route::get('media/{path?}', [\crudPackage\Http\Controllers\MediaController::class, 'index'])->where('path', '.*')->name('media.index')->middleware('checkPermission');
            Route::post('media/upload', [\crudPackage\Http\Controllers\MediaController::class, 'upload'])->name('media.upload')->middleware('checkPermission');
            Route::post('media/folder', [\crudPackage\Http\Controllers\MediaController::class, 'createFolder'])->name('media.createFolder')->middleware('checkPermission');
            Route::delete('media/delete/{path?}/{file?}', [\crudPackage\Http\Controllers\MediaController::class, 'delete'])->where('path', '.*')->name('media.delete')->middleware('checkPermission');

            Route::put('cruds/repeater/store/{crud}', [\crudPackage\Http\Controllers\CrudController::class, 'repeaterStore'])->name('cruds.repeater.store');
            Route::delete('cruds/repeater/delete/{crud_item}', [\crudPackage\Http\Controllers\CrudController::class, 'repeaterDestroy'])->name('cruds.repeater.destroy');
            Route::delete('cruds/relationship/delete/{crud_item}', [\crudPackage\Http\Controllers\CrudController::class, 'relationshipDestroy'])->name('cruds.relationship.destroy');
            Route::put('cruds/relationship/store/{crud}', [\crudPackage\Http\Controllers\CrudController::class, 'relationshipStore'])->name('cruds.relationship.store');
            Route::post('cruds/getColumns', [\crudPackage\Http\Controllers\CrudController::class, 'getColumns'])->name('cruds.create.columns');
            Route::get('cruds/create/{table_name}', [\crudPackage\Http\Controllers\CrudController::class, 'create'])->name('cruds.create.new');
            Route::resource('cruds', \crudPackage\Http\Controllers\CrudController::class)->middleware('checkPermission');

            self::getRoutes();
        });
    }
}