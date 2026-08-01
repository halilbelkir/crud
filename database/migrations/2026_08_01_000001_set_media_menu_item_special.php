<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menu_items')->where('route', 'media.index')->update(
            [
                'special' => 1,
            ]
        );
    }

    public function down(): void
    {

    }
};
