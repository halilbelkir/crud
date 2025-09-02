<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->unsignedBigInteger('role_group_id')->after('password');
            $table->foreign('role_group_id')->references('id')->on('role_groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
