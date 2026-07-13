<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename episode_order to feed_type on feeds table
        if (Schema::hasColumn('feeds', 'episode_order') && ! Schema::hasColumn('feeds', 'feed_type')) {
            Schema::table('feeds', function (Blueprint $table) {
                $table->renameColumn('episode_order', 'feed_type');
            });

            // Map old values to new feed_type values
            DB::table('feeds')->where('feed_type', 'chronological')->update(['feed_type' => 'static']);
            DB::table('feeds')->where('feed_type', 'newest_first')->update(['feed_type' => 'append']);

            // Change the column default from 'newest_first' to 'append'
            Schema::table('feeds', function (Blueprint $table) {
                $table->string('feed_type', 20)->default('append')->change();
            });
        }

        // Add display_date to library_items
        if (! Schema::hasColumn('library_items', 'display_date')) {
            Schema::table('library_items', function (Blueprint $table) {
                $table->date('display_date')->nullable()->after('published_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('library_items', 'display_date')) {
            Schema::table('library_items', function (Blueprint $table) {
                $table->dropColumn('display_date');
            });
        }

        if (Schema::hasColumn('feeds', 'feed_type') && ! Schema::hasColumn('feeds', 'episode_order')) {
            // Reverse the value mapping
            DB::table('feeds')->where('feed_type', 'static')->update(['feed_type' => 'chronological']);
            DB::table('feeds')->where('feed_type', 'append')->update(['feed_type' => 'newest_first']);

            Schema::table('feeds', function (Blueprint $table) {
                $table->renameColumn('feed_type', 'episode_order');
            });
        }
    }
};
