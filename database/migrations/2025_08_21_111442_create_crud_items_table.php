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
        Schema::create('crud_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('column_name')->nullable();
            $table->json('detail')->nullable();
            $table->foreignId('crud_id')->index()->constrained('cruds');
            $table->foreignId('form_type_id')->index()->constrained('form_types');
            $table->smallInteger('required')->index()->default(0)->nullable();
            $table->smallInteger('browse')->index()->default(0)->nullable();
            $table->smallInteger('read')->index()->default(0)->nullable();
            $table->smallInteger('edit')->index()->default(0)->nullable();
            $table->smallInteger('add')->index()->default(0)->nullable();
            $table->smallInteger('delete')->index()->default(0)->nullable();
            $table->smallInteger('status')->index()->default(1)->nullable();
            $table->integer('order')->index()->default(99)->nullable();
            $table->smallInteger('relationship')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crud_items');
    }
};
