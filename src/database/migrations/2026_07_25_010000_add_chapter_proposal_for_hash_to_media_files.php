<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            // Hash of the transcript that chapter_proposal was generated from, so repeated
            // generation jobs for the same transcript can skip the (costly, redundant) LLM call.
            $table->string('chapter_proposal_for_hash', 64)->nullable()->after('chapter_proposal');
        });
    }

    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropColumn('chapter_proposal_for_hash');
        });
    }
};
