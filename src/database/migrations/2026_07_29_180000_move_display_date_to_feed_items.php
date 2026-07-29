<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feed_items', function (Blueprint $table) {
            $table->date('display_date')->nullable();
        });

        DB::table('feed_items')->update([
            'display_date' => DB::raw('(select display_date from library_items where library_items.id = feed_items.library_item_id)'),
        ]);

        Schema::table('library_items', function (Blueprint $table) {
            $table->dropColumn('display_date');
        });
    }

    public function down(): void
    {
        Schema::table('library_items', function (Blueprint $table) {
            $table->date('display_date')->nullable();
        });

        DB::table('library_items')->update([
            'display_date' => DB::raw('(select display_date from feed_items where feed_items.library_item_id = library_items.id and display_date is not null limit 1)'),
        ]);

        Schema::table('feed_items', function (Blueprint $table) {
            $table->dropColumn('display_date');
        });
    }
};
