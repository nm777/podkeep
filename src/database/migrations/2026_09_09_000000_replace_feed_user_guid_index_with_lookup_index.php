<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropIndex(['user_guid']);
            $table->index(['user_guid', 'slug'], 'feeds_user_guid_slug_index');
        });
    }

    public function down(): void
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropIndex('feeds_user_guid_slug_index');
            $table->index('user_guid');
        });
    }
};
