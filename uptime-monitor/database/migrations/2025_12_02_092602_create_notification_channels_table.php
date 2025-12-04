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
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama channel (misal: Telegram Notif)
            $table->string('type', 50); // Tipe: telegram, discord
            $table->json('config'); // Token/API key/webhook URL
            $table->foreignId('created_by')->constrained('users'); // Relasi ke users.id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_channels');
    }
};
