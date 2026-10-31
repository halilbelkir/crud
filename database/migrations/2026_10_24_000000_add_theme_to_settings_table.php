<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table)
        {
            $table->string('theme', 50)->after('font')->default('default')->nullable()->comment('Paketteki resources/themes klasör adı; yalnızca theme.json içinde ready=true olan temalar seçilebilir, geçersiz değer default temaya düşer');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table)
        {
            $table->dropColumn('theme');
        });
    }
};
