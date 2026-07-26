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
        Schema::table('media_files', function (Blueprint $table) {
            $table->json('transcript')->nullable()->after('duration');
            $table->string('chapter_generation_status', 16)->nullable()->after('transcript');
            $table->json('chapter_proposal')->nullable()->after('chapter_generation_status');
            $table->text('chapter_generation_error')->nullable()->after('chapter_proposal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropColumn(['transcript', 'chapter_generation_status', 'chapter_proposal', 'chapter_generation_error']);
        });
    }
};
