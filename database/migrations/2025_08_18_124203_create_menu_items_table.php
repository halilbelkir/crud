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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->foreignId('menu_id')->index()->constrained('menus');
            $table->foreignId('parent_id')->default(0)->index();
            $table->integer('order')->index()->default(99)->nullable();
            $table->smallInteger('dynamic_route')->index()->default(0)->nullable();
            $table->smallInteger('target')->index()->default(0)->nullable();
            $table->smallInteger('status')->index()->default(1)->nullable();
            $table->smallInteger('main')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
