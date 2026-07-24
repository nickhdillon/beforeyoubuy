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
        Schema::table('collection_items', function (Blueprint $table) {
            $table->dropColumn(['brand', 'model_number', 'acquired_on']);
            $table->decimal('rating', 2, 1)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_items', function (Blueprint $table) {
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->date('acquired_on')->nullable();
            $table->unsignedTinyInteger('rating')->nullable()->change();
        });
    }
};
