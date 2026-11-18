<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('email');
            $table->string('phone', 20)->nullable()->after('avatar_path');
            $table->json('preferences')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('previous_login_at')->nullable();
        });

        // Pengaturan sekolah (admin): pasangan kunci-nilai
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_path', 'phone', 'preferences', 'last_login_at', 'previous_login_at']);
        });
    }
};
