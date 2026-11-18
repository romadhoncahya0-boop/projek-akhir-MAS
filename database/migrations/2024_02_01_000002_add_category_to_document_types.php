<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kategori dipakai sebagai tab filter di halaman "Dokumen Saya"
     * (Rapor & Transkrip / Surat Keterangan / Sertifikat & Piagam / Berkas Identitas).
     */
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->string('category')->default('Berkas Identitas')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
