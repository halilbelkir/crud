<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $column = DB::selectOne("SHOW COLUMNS FROM roles WHERE Field = 'crud_id'");

        if ($column && $column->Null === 'NO')
        {
            Schema::table('roles', function (Blueprint $table)
            {
                $table->unsignedBigInteger('crud_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {

    }
};
