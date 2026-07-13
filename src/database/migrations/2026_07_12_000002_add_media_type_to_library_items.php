<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('library_items', 'media_type')) {
            Schema::table('library_items', function (Blueprint $table) {
                $table->string('media_type', 10)->default('audio')->after('source_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('library_items', 'media_type')) {
            Schema::table('library_items', function (Blueprint $table) {
                $table->dropColumn('media_type');
            });
        }
    }
};
