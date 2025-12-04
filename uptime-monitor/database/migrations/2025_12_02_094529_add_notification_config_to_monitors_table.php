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
        Schema::table('monitors', function (Blueprint $table) {
            $table->integer('notify_after_retries')->default(1)->after('retries'); // FR-16: Anti-spam
            $table->integer('consecutive_failures')->default(0)->after('notify_after_retries'); // Track consecutive failures
            $table->json('notification_channels')->nullable()->after('consecutive_failures'); // Channel IDs for this monitor
            $table->timestampTz('last_notification_sent')->nullable()->after('notification_channels'); // Track last notification
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitors', function (Blueprint $table) {
            $table->dropColumn(['notify_after_retries', 'consecutive_failures', 'notification_channels', 'last_notification_sent']);
        });
    }
};
