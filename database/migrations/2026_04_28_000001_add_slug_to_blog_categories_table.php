<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('blog_categories', 'slug')) {
            Schema::table('blog_categories', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        $categories = DB::table('blog_categories')
            ->select('id', 'name', 'slug')
            ->orderBy('id')
            ->get();

        $usedSlugs = DB::table('blog_categories')
            ->whereNotNull('slug')
            ->pluck('slug')
            ->all();

        foreach ($categories as $category) {
            if (!empty($category->slug)) {
                continue;
            }

            $slug = slugify($category->name ?: ('category-' . $category->id));

            if (in_array($slug, $usedSlugs, true)) {
                $slug .= '-' . $category->id;
            }

            DB::table('blog_categories')
                ->where('id', $category->id)
                ->update(['slug' => $slug]);

            $usedSlugs[] = $slug;
        }

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};