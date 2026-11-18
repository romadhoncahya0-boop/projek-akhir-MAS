<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel permohonan dokumen dari siswa (halaman "Ajukan Dokumen").
     * Alur stage: diajukan -> revisi (opsional, bolak-balik ke siswa)
     *          -> menunggu_tte -> selesai
     *          (atau ditolak di tahap mana pun oleh TU/Kepala Sekolah)
     *
     * Saat stage = selesai, sistem membuat baris nyata di tabel `documents`
     * (lihat DocumentRequest::issueDocument()) sehingga dokumen resmi muncul
     * di "Dokumen Saya" siswa — bukan data dummy.
     */
    public function up(): void
    {
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained();

            $table->string('service_name');
            $table->string('purpose');
            $table->unsignedTinyInteger('physical_copies')->default(0);
            $table->boolean('digital_copy')->default(true);
            $table->text('notes')->nullable();

            $table->string('support_file_path');
            $table->string('additional_file_path')->nullable();

            $table->enum('stage', ['diajukan', 'revisi', 'menunggu_tte', 'selesai', 'ditolak'])
                ->default('diajukan');
            $table->text('tu_notes')->nullable();
            $table->timestamp('sla_due_at')->nullable();

            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();

            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_hash')->nullable();

            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
