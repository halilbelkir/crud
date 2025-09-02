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
        Schema::table('cruds', function (Blueprint $table) {
            $table->string('table_name')->nullable()->after('slug');;
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
        //
    }
};
