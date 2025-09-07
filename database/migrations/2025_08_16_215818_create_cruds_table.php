<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cruds', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->unique();
            $table->string('table_name')->nullable();
            $table->string('display_single')->nullable();
            $table->string('display_plural')->nullable();
            $table->string('icon')->nullable();
            $table->string('model')->nullable();
            $table->text('content')->nullable();
            $table->json('area_1')->nullable();
            $table->smallInteger('status')->index()->default(1)->nullable();
            $table->smallInteger('main')->index()->default(0)->nullable();
            $table->smallInteger('only_edit')->index()->default(0)->nullable();
            $table->timestamps();
        });

        DB::table('cruds')->insert(
            [
                [
                    'title'      => 'Kullanıcılar',
                    'slug'       => 'users',
                    'table_name' => 'users',
                    'status'     => 1,
                    'main'       => 1
                ],
                [
                    'title'      => 'Yetkiler',
                    'slug'       => 'role-groups',
                    'table_name' => 'role-groups',
                    'status'     => 1,
                    'main'       => 1
                ],
                [
                    'title'      => 'Menü',
                    'slug'       => 'menus',
                    'table_name' => 'menus',
                    'status'     => 1,
                    'main'       => 1
                ],
                [
                    'title'      => 'Modüller',
                    'slug'       => 'cruds',
                    'table_name' => 'cruds',
                    'status'     => 1,
                    'main'       => 1
                ]
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cruds');
    }
};
