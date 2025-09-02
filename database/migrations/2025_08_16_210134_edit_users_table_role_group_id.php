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
                    'password'      => '$2y$10$T8.uLp2O5jFm8y.Qn4.X0O.a7jZ.fT3g.q3z.fP0wL4jR.sP9',
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
