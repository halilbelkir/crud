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
        Schema::table('menu_items', function (Blueprint $table)
        {
            $table->smallInteger('special')->default(0)->after('main');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
