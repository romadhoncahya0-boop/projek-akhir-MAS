<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentRequest extends Model
{
    protected $fillable = [
        'ticket_number',
        'student_id',
        'document_type_id',
        'service_name',
        'purpose',
        'physical_copies',
        'digital_copy',
        'notes',
        'support_file_path',
        'additional_file_path',
        'stage',
        'tu_notes',
        'sla_due_at',
        'processed_by',
        'processed_at',
        'signed_by',
        'signed_at',
        'signed_hash',
        'document_id',
    ];

    protected $casts = [
        'digital_copy' => 'boolean',
        'sla_due_at' => 'datetime',
        'processed_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /** URL lampiran (kind: support|additional) lewat route terautentikasi; null jika tidak ada. */
    public function fileUrl(string $kind = 'support', bool $download = false): ?string
    {
        $path = $kind === 'additional' ? $this->additional_file_path : $this->support_file_path;
        if (! $path) {
            return null;
        }

        return route('files.request', array_filter([
            'documentRequest' => $this->id, 'kind' => $kind, 'unduh' => $download ? 1 : null,
        ]), false);
    }

    public function fileExists(string $kind = 'support'): bool
    {
        return \App\Support\StoredFile::exists($kind === 'additional' ? $this->additional_file_path : $this->support_file_path);
    }

    public function fileIsImage(string $kind = 'support'): bool
    {
        return \App\Support\StoredFile::isImage($kind === 'additional' ? $this->additional_file_path : $this->support_file_path);
    }

    public static function currentAcademicYear(): string
    {
        $year = (int) now()->format('Y');
        $start = now()->month >= 7 ? $year : $year - 1;

        return $start.'/'.($start + 1);
    }

    public static function generateTicketNumber(): string
    {
        return 'REQ-'.now()->format('Y').'-'.str_pad((string) (static::max('id') + 1), 4, '0', STR_PAD_LEFT);
    }

    public function stageLabel(): string
    {
        return match ($this->stage) {
            'diajukan' => 'Dalam Proses - Verifikasi Tata Usaha',
            'revisi' => 'Perlu Revisi Dokumen',
            'menunggu_tte' => 'Menunggu Tanda Tangan Kepala Sekolah',
            'selesai' => 'Disetujui & Siap Diunduh',
            'ditolak' => 'Ditolak',
            default => ucfirst($this->stage),
        };
    }

    /**
     * Dipanggil saat Kepala Sekolah menandatangani permohonan (stage -> selesai).
     * Membuat dokumen resmi NYATA di tabel documents (bukan mockup) agar langsung
     * muncul di "Dokumen Saya" siswa, lengkap dengan hash TTE.
     */
    public function issueDocument(int $signedByUserId): Document
    {
        $hash = hash('sha256', $this->ticket_number.$this->student_id.now()->timestamp.Str::random(8));

        $document = Document::create([
            'student_id' => $this->student_id,
            'document_type_id' => $this->document_type_id,
            'document_number' => $this->ticket_number,
            'issue_date' => now()->toDateString(),
            'academic_year' => static::currentAcademicYear(),
            'file_path' => $this->support_file_path,
            'notes' => $this->purpose,
            'status' => 'verified',
            'uploaded_by' => $this->student->user_id,
            'verified_by' => $this->processed_by,
            'verified_at' => $this->processed_at ?? now(),
            'signed_hash' => $hash,
            'signed_at' => now(),
            'signed_by' => $signedByUserId,
        ]);

        $this->update([
            'stage' => 'selesai',
            'signed_by' => $signedByUserId,
            'signed_at' => now(),
            'signed_hash' => $hash,
            'document_id' => $document->id,
        ]);

        return $document;
    }
}
