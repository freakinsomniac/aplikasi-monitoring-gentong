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
            $table->string('group_name')->nullable()->after('name');
            $table->text('group_description')->nullable()->after('group_name');
            $table->json('group_config')->nullable()->after('group_description');
            $table->index('group_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitors', function (Blueprint $table) {
            $table->dropIndex(['group_name']);
            $table->dropColumn(['group_name', 'group_description', 'group_config']);
        });
    }
};
