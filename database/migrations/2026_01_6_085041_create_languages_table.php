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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('code')->nullable();
            $table->integer('order')->default(9999);
            $table->timestamps();
        });

        DB::table('languages')->insert(
            [
                [
                    'title' => 'Türkçe',
                    'code'  => 'tr',
                    'order' => 1,
                ],
                [
                    'title' => 'İngilizce',
                    'code'  => 'en',
                    'order' => 2,
                ],
                [
                    'title' => 'Almanca',
                    'code'  => 'de',
                    'order' => 3,
                ],
                [
                    'title' => 'Fransızca',
                    'code'  => 'fr',
                    'order' => 4,
                ],
                [
                    'title' => 'İspanyolca',
                    'code'  => 'es',
                    'order' => 5,
                ],
                [
                    'title' => 'İtalyanca',
                    'code'  => 'it',
                    'order' => 6,
                ],
                [
                    'title' => 'Portekizce',
                    'code'  => 'pt',
                    'order' => 7,
                ],
                [
                    'title' => 'Rusça',
                    'code'  => 'ru',
                    'order' => 8,
                ],
                [
                    'title' => 'Arapça',
                    'code'  => 'ar',
                    'order' => 9,
                ],
                [
                    'title' => 'Farsça',
                    'code'  => 'fa',
                    'order' => 10,
                ],
                [
                    'title' => 'Çince',
                    'code'  => 'zh',
                    'order' => 11,
                ],
                [
                    'title' => 'Japonca',
                    'code'  => 'ja',
                    'order' => 12,
                ],
                [
                    'title' => 'Korece',
                    'code'  => 'ko',
                    'order' => 13,
                ],
                [
                    'title' => 'Hintçe',
                    'code'  => 'hi',
                    'order' => 14,
                ],
                [
                    'title' => 'Urduca',
                    'code'  => 'ur',
                    'order' => 15,
                ],
                [
                    'title' => 'Bengalce',
                    'code'  => 'bn',
                    'order' => 16,
                ],
                [
                    'title' => 'Endonezce',
                    'code'  => 'id',
                    'order' => 17,
                ],
                [
                    'title' => 'Malayca',
                    'code'  => 'ms',
                    'order' => 18,
                ],
                [
                    'title' => 'Tayca',
                    'code'  => 'th',
                    'order' => 19,
                ],
                [
                    'title' => 'Vietnamca',
                    'code'  => 'vi',
                    'order' => 20,
                ],
                [
                    'title' => 'Lehçe',
                    'code'  => 'pl',
                    'order' => 21,
                ],
                [
                    'title' => 'Çekçe',
                    'code'  => 'cs',
                    'order' => 22,
                ],
                [
                    'title' => 'Slovakça',
                    'code'  => 'sk',
                    'order' => 23,
                ],
                [
                    'title' => 'Macarca',
                    'code'  => 'hu',
                    'order' => 24,
                ],
                [
                    'title' => 'Rumence',
                    'code'  => 'ro',
                    'order' => 25,
                ],
                [
                    'title' => 'Bulgarca',
                    'code'  => 'bg',
                    'order' => 26,
                ],
                [
                    'title' => 'Sırpça',
                    'code'  => 'sr',
                    'order' => 27,
                ],
                [
                    'title' => 'Hırvatça',
                    'code'  => 'hr',
                    'order' => 28,
                ],
                [
                    'title' => 'Boşnakça',
                    'code'  => 'bs',
                    'order' => 29,
                ],
                [
                    'title' => 'Yunanca',
                    'code'  => 'el',
                    'order' => 30,
                ],
                [
                    'title' => 'İbranice',
                    'code'  => 'he',
                    'order' => 31,
                ],
                [
                    'title' => 'İsveççe',
                    'code'  => 'sv',
                    'order' => 32,
                ],
                [
                    'title' => 'Norveççe',
                    'code'  => 'no',
                    'order' => 33,
                ],
                [
                    'title' => 'Danca',
                    'code'  => 'da',
                    'order' => 34,
                ],
                [
                    'title' => 'Fince',
                    'code'  => 'fi',
                    'order' => 35,
                ],
                [
                    'title' => 'Felemenkçe',
                    'code'  => 'nl',
                    'order' => 36,
                ],
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
