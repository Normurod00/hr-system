<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trusted_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45); // IPv4/IPv6
            $table->string('label')->nullable(); // "Офис Ташкент"
            $table->string('applies_to', 20)->default('admin'); // admin, all, specific_user
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['ip_address', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_ips');
    }
};
