<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DocumentRequestSeeder extends Seeder
{
    public function run(): void
    {
        $tu = User::where('role', 'admin')->first();
        $rizky = Student::where('nisn', '0068912345')->first();
        $annisa = Student::where('name', 'Annisa Nurul Aini')->first();
        $dimas = Student::where('name', 'Dimas Wahyu Pratama')->first();
        $clarissa = Student::where('name', 'Clarissa Aurelia')->first();
        $kevin = Student::where('name', 'Kevin Jonathan')->first();

        $rapor = DocumentType::where('code', 'RPR')->first();
        $ska = DocumentType::where('code', 'SKA')->first();
        $legalisir = DocumentType::where('code', 'LGL')->first();
        $trk = DocumentType::where('code', 'TRK')->first();
        $skbb = DocumentType::where('code', 'SKBB')->first();
        $piagam = DocumentType::where('code', 'PGM')->first();
        $snbp = DocumentType::where('code', 'SNBP')->first();

        $placeholder = fn (string $name) => Storage::disk('public')->put(
            'seed/'.$name,
            "Berkas contoh (data seed) untuk demo alur MAS.\nDihasilkan otomatis oleh DocumentRequestSeeder."
        ) ? 'seed/'.$name : 'seed/'.$name;

        // === Dokumen resmi yang sudah terbit (mengisi "Dokumen Saya") ===
        Document::create([
            'student_id' => $rizky->id, 'document_type_id' => $rapor->id,
            'document_number' => 'DOK-RPR-2025-01', 'issue_date' => '2025-01-15', 'academic_year' => '2024/2025',
            'file_path' => $placeholder('rapor-gasal-2024-2025.txt'), 'status' => 'verified',
            'uploaded_by' => $rizky->user_id, 'verified_by' => $tu->id, 'verified_at' => now(),
            'signed_hash' => hash('sha256', 'demo-rapor-gasal'), 'signed_at' => now(), 'signed_by' => $tu->id,
        ]);

        Document::create([
            'student_id' => $rizky->id, 'document_type_id' => $ska->id,
            'document_number' => '421.3/TU/SMAN1/IV/2025', 'issue_date' => '2025-04-18', 'academic_year' => '2024/2025',
            'file_path' => $placeholder('surat-keterangan-aktif.txt'), 'status' => 'verified',
            'uploaded_by' => $rizky->user_id, 'verified_by' => $tu->id, 'verified_at' => now(),
            'signed_hash' => hash('sha256', 'demo-ska'), 'signed_at' => now(), 'signed_by' => $tu->id,
        ]);

        Document::create([
            'student_id' => $rizky->id, 'document_type_id' => $trk->id,
            'document_number' => 'DOK-TRK-2025-88', 'issue_date' => '2025-02-10', 'academic_year' => '2024/2025',
            'file_path' => $placeholder('transkrip-nilai.txt'), 'status' => 'verified',
            'uploaded_by' => $rizky->user_id, 'verified_by' => $tu->id, 'verified_at' => now(),
        ]);

        Document::create([
            'student_id' => $rizky->id, 'document_type_id' => $skbb->id,
            'document_number' => '421.3/BK/SMAN1/I/2025', 'issue_date' => '2025-01-05', 'academic_year' => '2024/2025',
            'file_path' => $placeholder('skbb.txt'), 'status' => 'verified',
            'uploaded_by' => $rizky->user_id, 'verified_by' => $tu->id, 'verified_at' => now(),
        ]);

        Document::create([
            'student_id' => $rizky->id, 'document_type_id' => $piagam->id,
            'document_number' => 'SERT-OSK-2024', 'issue_date' => '2024-09-14', 'academic_year' => '2023/2024',
            'file_path' => $placeholder('piagam-osk.txt'), 'status' => 'verified',
            'uploaded_by' => $rizky->user_id, 'verified_by' => $tu->id, 'verified_at' => now(),
        ]);

        // === Antrean permohonan aktif (mengisi "Manajemen Permohonan") ===
        $requests = [
            ['student' => $rizky, 'type' => $snbp, 'service' => 'Surat Keterangan Rekomendasi SNBP & Legalisir Rapor', 'purpose' => 'Pendaftaran Mandiri Perguruan Tinggi Negeri', 'stage' => 'diajukan'],
            ['student' => $annisa, 'type' => $ska, 'service' => 'Surat Keterangan Siswa Aktif', 'purpose' => 'Keperluan SNPMB', 'stage' => 'diajukan'],
            ['student' => $dimas, 'type' => $legalisir, 'service' => 'Legalisir Ijazah & SKL Sementara', 'purpose' => 'Pendaftaran Kerja', 'stage' => 'revisi', 'tu_notes' => 'Scan ijazah asli buram/tidak terbaca stempel dinas.'],
            ['student' => $clarissa, 'type' => $trk, 'service' => 'Transkrip Nilai Sementara Beasiswa', 'purpose' => 'Pengajuan Beasiswa PIP', 'stage' => 'menunggu_tte'],
            ['student' => $kevin, 'type' => $skbb, 'service' => 'Surat Keterangan Berkelakuan Baik', 'purpose' => 'Syarat Organisasi Kepemudaan', 'stage' => 'diajukan'],
        ];

        foreach ($requests as $i => $r) {
            if (! $r['student']) {
                continue;
            }

            DocumentRequest::create([
                'ticket_number' => 'REQ-2025-08'.(41 - $i),
                'student_id' => $r['student']->id,
                'document_type_id' => $r['type']->id,
                'service_name' => $r['service'],
                'purpose' => $r['purpose'],
                'physical_copies' => 3,
                'digital_copy' => true,
                'support_file_path' => $placeholder('permohonan-'.$r['student']->id.'.txt'),
                'stage' => $r['stage'],
                'tu_notes' => $r['tu_notes'] ?? null,
                'processed_by' => in_array($r['stage'], ['revisi', 'menunggu_tte']) ? $tu->id : null,
                'processed_at' => in_array($r['stage'], ['revisi', 'menunggu_tte']) ? now() : null,
                'sla_due_at' => now()->addWeekdays(3),
            ]);
        }
    }
}
