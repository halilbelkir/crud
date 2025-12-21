<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('logo')->nullable();
            $table->string('icon')->nullable();
            $table->string('loader')->nullable();
            $table->string('bg_image')->nullable();
            $table->string('color_1')->nullable();
            $table->string('color_2')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert(
            [
                [
                    'title'    => 'Zaurac Teknoloji',
                    'subtitle' => 'Yönetim Paneline Hoş Geldiniz',
                ]
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
