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
            $table->string('font')->after('color_2')->nullable();
        });
    }

    public function down(): void
    {

    }
};
