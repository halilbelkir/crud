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
        Schema::create('cruds', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->unique();
            $table->string('display_single')->nullable();
            $table->string('display_plural')->nullable();
            $table->string('icon')->nullable();
            $table->string('model')->nullable();
            $table->text('content')->nullable();
            $table->json('area_1')->nullable();
            $table->smallInteger('status')->index()->default(1)->nullable();
            $table->smallInteger('main')->index()->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cruds');
    }
};
