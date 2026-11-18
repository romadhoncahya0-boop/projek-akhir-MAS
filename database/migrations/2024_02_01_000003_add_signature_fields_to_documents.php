<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Field tanda tangan elektronik (TTE) resmi untuk dokumen yang sudah
     * disahkan Kepala Sekolah, dipakai di badge "TTE BSrE" pada Dokumen Saya.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('signed_hash')->nullable()->after('verified_at');
            $table->timestamp('signed_at')->nullable()->after('signed_hash');
            $table->foreignId('signed_by')->nullable()->after('signed_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signed_by');
            $table->dropColumn(['signed_hash', 'signed_at']);
        });
    }
};
