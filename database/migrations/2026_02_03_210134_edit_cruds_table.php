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
        Schema::table('cruds', function (Blueprint $table)
        {
            $table->json('other_route')->after('content')->nullable();
        });

        DB::table('cruds')->update(
            [
                "other_route" => json_encode([]),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
