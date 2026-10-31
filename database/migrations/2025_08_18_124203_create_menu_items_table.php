<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->foreignId('menu_id')->index()->constrained('menus');
            $table->foreignId('parent_id')->default(0)->index();
            $table->integer('order')->index()->default(99)->nullable();
            $table->smallInteger('dynamic_route')->index()->default(0)->nullable();
            $table->smallInteger('target')->index()->default(0)->nullable();
            $table->smallInteger('status')->index()->default(1)->nullable();
            $table->smallInteger('main')->default(0);
            $table->timestamps();
        });

        DB::table('menu_items')->insert(
            [
                [
                    'title'         => 'Modüller',
                    'route'         => 'cruds.index',
                    'icon'          => 'file',
                    'menu_id'       => 1,
                    'order'         => 1,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ],
                [
                    'title'         => 'Yetkiler',
                    'route'         => 'role-groups.index',
                    'icon'          => 'shield-check',
                    'menu_id'       => 1,
                    'order'         => 2,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ],
                [
                    'title'         => 'Kullanıcılar',
                    'route'         => 'users.index',
                    'icon'          => 'users',
                    'menu_id'       => 1,
                    'order'         => 3,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ],
                [
                    'title'         => 'Menü',
                    'route'         => 'menus.index',
                    'icon'          => 'menu',
                    'menu_id'       => 1,
                    'order'         => 4,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ],
                [
                    'title'         => 'Ayarlar',
                    'route'         => 'settings.index',
                    'icon'          => 'settings',
                    'menu_id'       => 1,
                    'order'         => 5,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ],
                [
                    'title'         => 'Geçmiş',
                    'route'         => 'logs.index',
                    'icon'          => 'history',
                    'menu_id'       => 1,
                    'order'         => 6,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ],
                [
                    'title'         => 'Media',
                    'route'         => 'media.index',
                    'icon'          => 'upload',
                    'menu_id'       => 1,
                    'order'         => 7,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ]
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
