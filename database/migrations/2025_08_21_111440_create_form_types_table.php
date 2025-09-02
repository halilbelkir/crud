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
        Schema::create('form_types', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('key')->nullable();
            $table->string('group',30)->nullable();
            $table->smallInteger('status')->index()->default(1)->nullable();
            $table->timestamps();
        });

        DB::table('form_types')->insert($this->insertData());
    }

    public function insertData()
    {
        return
            [
                [
                    'title'  => 'Çoklu Seçim (checkbox)',
                    'key'    => 'checkbox',
                    'group'  => 'checkbox',
                    'status' => 1,
                ],
                [
                    'title'  => 'Renk',
                    'key'    => 'color',
                    'group'  => 'input',
                    'status' => 1,
                ],
                [
                    'title'  => 'Tarih',
                    'key'    => 'date',
                    'group'  => 'input',
                    'status' => 1,
                ],
                [
                    'title'  => 'Tarih & Saat',
                    'key'    => 'datetime',
                    'group'  => 'input',
                    'status' => 1,
                ],
                [
                    'title'  => 'Dosya',
                    'key'    => 'file',
                    'group'  => 'input',
                    'status' => 1,
                ],
                [
                    'title'  => 'Resim',
                    'key'    => 'image',
                    'group'  => 'input',
                    'status' => 1,
                ],
                [
                    'title'  => 'Numara',
                    'key'    => 'number',
                    'group'  => 'input',
                    'status' => 1,
                ],
                [
                    'title'  => 'Tekli Seçim (Radio)',
                    'key'    => 'radio',
                    'group'  => 'radio',
                    'status' => 1,
                ],
                [
                    'title'  => 'Editör',
                    'key'    => 'editor',
                    'group'  => 'textarea',
                    'status' => 1,
                ],
                [
                    'title'  => 'Geniş Yazı Alanı',
                    'key'    => 'textarea',
                    'group'  => 'textarea',
                    'status' => 1,
                ],
                [
                    'title'  => 'Yazı Alanı',
                    'key'    => 'text',
                    'group'  => 'input',
                    'status' => 1,
                ],
                [
                    'title'  => 'Tekli Seçme (Selectbox)',
                    'key'    => 'select',
                    'group'  => 'select',
                    'status' => 1,
                ],
                [
                    'title'  => 'Gizli',
                    'key'    => 'hidden',
                    'group'  => 'input',
                    'status' => 1,
                ],
                [
                    'title'  => 'Şifre',
                    'key'    => 'password',
                    'group'  => 'input',
                    'status' => 1,
                ],
                [
                    'title'  => 'Tekli Seçim (switch)',
                    'key'    => 'switch',
                    'group'  => 'checkbox',
                    'status' => 1,
                ],
                [
                    'title'  => 'Tekli Seçme (Select2)',
                    'key'    => 'select2',
                    'group'  => 'select',
                    'status' => 1,
                ],
            ];
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_types');
    }
};
