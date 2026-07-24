<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('public_id');
        });

        $usedSlugs = [];

        DB::table('collections')->orderBy('id')->get(['id', 'name'])->each(function (object $collection) use (&$usedSlugs): void {
            $baseSlug = Str::slug($collection->name) ?: 'collection';
            $slug = $baseSlug;
            $suffix = 2;

            while (isset($usedSlugs[$slug])) {
                $slug = $baseSlug.'-'.$suffix;
                $suffix++;
            }

            DB::table('collections')->where('id', $collection->id)->update(['slug' => $slug]);
            $usedSlugs[$slug] = true;
        });

        Schema::table('collections', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->ulid('public_id')->nullable()->after('id');
        });

        DB::table('collections')->orderBy('id')->pluck('id')->each(function (int $id): void {
            DB::table('collections')->where('id', $id)->update(['public_id' => (string) Str::ulid()]);
        });

        Schema::table('collections', function (Blueprint $table) {
            $table->ulid('public_id')->nullable(false)->unique()->change();
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
