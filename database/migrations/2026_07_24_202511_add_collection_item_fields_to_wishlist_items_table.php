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
        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('wishlist_id');
            $table->decimal('rating', 2, 1)->nullable()->after('quantity');
            $table->string('name', 120)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'rating']);
            $table->string('name', 120)->nullable(false)->change();
        });
    }
};
