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
        Schema::create('monitor_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained('monitors')->onDelete('cascade'); // Relasi ke monitors.id
            $table->timestampTz('checked_at'); // Waktu pengecekan dilakukan
            $table->string('status', 20); // up/down/unknown
            $table->integer('latency_ms')->nullable(); // Latensi (ms)
            $table->integer('http_status')->nullable(); // Status HTTP (jika type=HTTP)
            $table->text('error_message')->nullable(); // Pesan error (jika gagal)
            $table->bigInteger('response_size')->nullable(); // Ukuran respons
            $table->string('region')->nullable(); // Wilayah worker yang melakukan check
            $table->json('meta')->nullable(); // Info tambahan: header, body snippet, redirect trace
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['monitor_id', 'checked_at']);
            $table->index(['status', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitor_checks');
    }
};
