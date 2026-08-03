<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->boolean('is_public')->default(false);
        });

        DB::table('media_files')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('library_items')
                    ->join('feed_items', 'feed_items.library_item_id', '=', 'library_items.id')
                    ->join('feeds', 'feeds.id', '=', 'feed_items.feed_id')
                    ->whereColumn('library_items.media_file_id', 'media_files.id')
                    ->whereColumn('library_items.user_id', 'media_files.user_id')
                    ->whereColumn('feeds.user_id', 'media_files.user_id')
                    ->where('feeds.is_public', true);
            })
            ->update(['is_public' => true]);
    }

    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
