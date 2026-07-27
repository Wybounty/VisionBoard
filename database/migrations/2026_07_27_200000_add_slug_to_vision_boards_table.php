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
        Schema::table('vision_boards', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        $visionBoards = DB::table('vision_boards')
            ->select('id', 'title')
            ->orderBy('id')
            ->get();

        $usedSlugs = [];

        foreach ($visionBoards as $visionBoard) {
            $baseSlug = Str::slug($visionBoard->title) ?: 'vision-board';
            $slug = $baseSlug;
            $counter = 2;

            while (in_array($slug, $usedSlugs, true) || DB::table('vision_boards')->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            DB::table('vision_boards')
                ->where('id', $visionBoard->id)
                ->update(['slug' => $slug]);

            $usedSlugs[] = $slug;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vision_boards', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
