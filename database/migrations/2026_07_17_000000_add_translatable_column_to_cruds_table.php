<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cruds', function (Blueprint $table)
        {
            $table->smallInteger('translatable')->index()->default(1)->nullable()->after('only_edit');
        });
    }

    public function down(): void
    {
        Schema::table('cruds', function (Blueprint $table)
        {
            $table->dropColumn('translatable');
        });
    }
};
