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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained('monitors')->onDelete('cascade'); // Relasi ke monitors.id
            $table->timestampTz('started_at'); // Awal downtime
            $table->timestampTz('ended_at')->nullable(); // Akhir downtime
            $table->boolean('resolved')->default(false); // Sudah selesai atau belum
            $table->text('description')->nullable(); // Catatan insiden
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['monitor_id', 'started_at']);
            $table->index(['resolved', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
