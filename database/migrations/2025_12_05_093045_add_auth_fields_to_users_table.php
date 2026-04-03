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
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 20)->nullable()->unique()->after('phone')->comment('ИНН/ПИНФЛ пользователя');
            $table->string('oneid_id', 100)->nullable()->unique()->after('pin')->comment('ID пользователя в ONE ID');
            $table->string('last_login_method', 20)->nullable()->after('oneid_id')->comment('Последний способ входа: password, oneid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pin', 'oneid_id', 'last_login_method']);
        });
    }
};
