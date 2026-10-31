<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_templates', function (Blueprint $table)
        {
            $table->id();
            $table->string('key', 50)->unique()->comment('config crudPanel.mail_templates anahtarı (invite, password_reset); tanımı olmayan anahtar kullanılmaz');
            $table->string('subject')->nullable()->comment('boşsa config içindeki varsayılan konu kullanılır');
            $table->string('heading')->nullable()->comment('boşsa varsayılan başlık kullanılır');
            $table->longText('content')->nullable()->comment('boşsa varsayılan metin kullanılır; {name} gibi değişkenler gönderimde doldurulur');
            $table->string('button', 100)->nullable()->comment('boşsa varsayılan buton yazısı kullanılır; bağlantı her zaman butona konur');
            $table->text('note')->nullable()->comment('boşsa varsayılan not kullanılır');
            $table->timestamps();
        });

        if (! DB::table('menu_items')->where('route', 'mail-templates.index')->exists())
        {
            DB::table('menu_items')->insert(
            [
                'title'         => 'E-posta Şablonları',
                'route'         => 'mail-templates.index',
                'icon'          => 'mail',
                'menu_id'       => 1,
                'order'         => (int) DB::table('menu_items')->where('route', 'settings.index')->value('order') + 1,
                'dynamic_route' => 1,
                'main'          => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menu_items')->where('route', 'mail-templates.index')->delete();

        Schema::dropIfExists('mail_templates');
    }
};
