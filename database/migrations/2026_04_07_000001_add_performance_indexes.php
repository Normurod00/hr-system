<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add composite indexes that don't already exist
        // Single-column indexes on status, created_at, match_score already exist

        // Applications — composite index for dashboard/analytics queries
        Schema::table('applications', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'applications_status_created_composite');
            $table->index(['vacancy_id', 'status'], 'applications_vacancy_status_composite');
        });

        // AI logs — composite for time-range + status queries
        Schema::table('ai_logs', function (Blueprint $table) {
            $table->index(['created_at', 'status'], 'ai_logs_created_status_composite');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('applications_status_created_composite');
            $table->dropIndex('applications_vacancy_status_composite');
        });

        Schema::table('ai_logs', function (Blueprint $table) {
            $table->dropIndex('ai_logs_created_status_composite');
        });
    }
};
