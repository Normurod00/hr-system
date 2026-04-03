<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hr_id', 'employee_id']);
            $table->index(['employee_id']);
            $table->index(['last_message_at']);
        });

        Schema::create('staff_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_chat_id')->constrained('staff_chats')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['staff_chat_id', 'created_at']);
            $table->index(['staff_chat_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_chat_messages');
        Schema::dropIfExists('staff_chats');
    }
};
