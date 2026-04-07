<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'sqlite') {
            return; // SQLite does not support ALTER TABLE constraints
        }

        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('candidate', 'employee', 'hr', 'admin'))");
    }

    public function down(): void
    {
        if (config('database.default') === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('candidate', 'hr', 'admin'))");
    }
};
