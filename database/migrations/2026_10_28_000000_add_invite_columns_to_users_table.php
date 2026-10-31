<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->smallInteger('force_password_change')->default(0)->index()->after('status')->comment('1=davet bekliyor, kullanıcı şifresini henüz belirlemedi; şifre belirlenince veya sıfırlanınca 0 olur');
            $table->timestamp('invited_at')->nullable()->after('force_password_change')->comment('son davet e-postasının gönderildiği an; tekrar gönderimde güncellenir');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->dropColumn(['force_password_change', 'invited_at']);
        });
    }
};
