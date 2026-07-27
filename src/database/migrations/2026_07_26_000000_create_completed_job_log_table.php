<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('completed_job_log', function (Blueprint $table) {
            $table->id();
            $table->string('job_type', 255);
            $table->string('queue', 255);
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('completed_job_log');
    }
};
