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
        Schema::table('users', function (Blueprint $table)
        {
            $table->smallInteger('status')->default(1)->after('password');
            $table->unsignedBigInteger('role_group_id')->after('password');
            $table->foreign('role_group_id')->references('id')->on('role_groups');
        });

        DB::table('users')->insert(
            [
                [
                    'name'          => 'Ana Kullanıcı',
                    'email'         => 'admin@zaurac.io',
                    'password'      => '$2y$12$m3/l6viIi3KsXAvKWzi2/.ybS6cs/bNjz77ZTNf5Sujc5wRA6fY9C',
                    'role_group_id' => 1,
                    'status'        => 1,
                ]
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
