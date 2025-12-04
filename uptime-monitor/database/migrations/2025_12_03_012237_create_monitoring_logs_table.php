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
        Schema::create('monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained('monitors')->cascadeOnDelete();
            $table->string('event_type'); // check_start, check_complete, check_failed, status_change, etc
            $table->string('status')->nullable(); // up, down, unknown
            $table->json('log_data'); // JSON formatted log details
            $table->decimal('response_time', 8, 3)->nullable(); // milliseconds
            $table->string('error_message', 1000)->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();
            
            $table->index(['monitor_id', 'logged_at']);
            $table->index(['event_type', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_logs');
    }
};
