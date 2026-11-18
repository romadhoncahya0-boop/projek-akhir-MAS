<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'document_type_id',
        'document_number',
        'issue_date',
        'academic_year',
        'file_path',
        'notes',
        'status',
        'uploaded_by',
        'verified_by',
        'verified_at',
        'signed_hash',
        'signed_at',
        'signed_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'verified_at' => 'datetime',
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

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /** URL berkas lewat route terautentikasi; null jika belum ada berkas. */
    public function fileUrl(bool $download = false): ?string
    {
        return $this->file_path
            ? route('files.document', array_filter(['document' => $this->id, 'unduh' => $download ? 1 : null]), false)
            : null;
    }

    public function isSigned(): bool
    {
        return ! empty($this->signed_hash);
    }
}
