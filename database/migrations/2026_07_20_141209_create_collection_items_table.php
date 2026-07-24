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
        Schema::create('collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->date('acquired_on')->nullable();
            $table->timestamps();

            $table->index(['collection_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_items');
    }
};
