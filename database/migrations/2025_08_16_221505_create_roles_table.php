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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_group_id')->index()->constrained('role_groups');
            $table->foreignId('crud_id')->index()->constrained('cruds');
            $table->smallInteger('browse')->index()->default(0)->nullable();
            $table->smallInteger('read')->index()->default(0)->nullable();
            $table->smallInteger('edit')->index()->default(0)->nullable();
            $table->smallInteger('add')->index()->default(0)->nullable();
            $table->smallInteger('delete')->index()->default(0)->nullable();
            $table->timestamps();
        });

        DB::table('roles')->insert(
            [
                [
                    'role_group_id' => 1,
                    'crud_id'       => 1,
                    'browse'        => 1,
                    'read'          => 1,
                    'edit'          => 1,
                    'add'           => 1,
                    'delete'        => 1,
                ],
                [
                    'role_group_id' => 1,
                    'crud_id'       => 2,
                    'browse'        => 1,
                    'read'          => 1,
                    'edit'          => 1,
                    'add'           => 1,
                    'delete'        => 1,
                ],
                [
                    'role_group_id' => 1,
                    'crud_id'       => 3,
                    'browse'        => 1,
                    'read'          => 1,
                    'edit'          => 1,
                    'add'           => 1,
                    'delete'        => 1,
                ],
                [
                    'role_group_id' => 1,
                    'crud_id'       => 4,
                    'browse'        => 1,
                    'read'          => 1,
                    'edit'          => 1,
                    'add'           => 1,
                    'delete'        => 1,
                ],
                [
                    'role_group_id' => 1,
                    'crud_id'       => 5,
                    'browse'        => 1,
                    'read'          => 0,
                    'edit'          => 1,
                    'add'           => 0,
                    'delete'        => 0,
                ],
                [
                    'role_group_id' => 1,
                    'crud_id'       => 6,
                    'browse'        => 1,
                    'read'          => 1,
                    'edit'          => 1,
                    'add'           => 0,
                    'delete'        => 0,
                ]
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
