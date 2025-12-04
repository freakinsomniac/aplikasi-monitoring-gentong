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
        Schema::create('monitor_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained('monitors')->onDelete('cascade'); // Relasi ke monitors.id
            $table->timestampTz('period_start'); // Awal periode agregasi
            $table->timestampTz('period_end'); // Akhir periode
            $table->decimal('avg_response_time_ms', 8, 2)->nullable(); // Rata-rata response time
            $table->decimal('p95_response_time_ms', 8, 2)->nullable(); // Percentile 95 response time
            $table->bigInteger('uptime_seconds')->default(0); // Total detik UP
            $table->bigInteger('downtime_seconds')->default(0); // Total detik DOWN
            $table->integer('checks_count')->default(0); // Banyaknya check
            $table->timestamps();
            
            // Index untuk performa query agregasi
            $table->index(['monitor_id', 'period_start']);
            $table->index(['period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitor_metrics');
    }
};
