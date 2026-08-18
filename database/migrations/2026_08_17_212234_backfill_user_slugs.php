<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function (object $user): void {
                $baseSlug = Str::slug($user->name) ?: 'user';
                $slug = $baseSlug;
                $suffix = 2;

                while (DB::table('users')->where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$suffix;
                    $suffix++;
                }

                DB::table('users')->where('id', $user->id)->update(['slug' => $slug]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // User slugs are retained until the schema migration removes the column.
    }
};
