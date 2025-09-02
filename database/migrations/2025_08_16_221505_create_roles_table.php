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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
