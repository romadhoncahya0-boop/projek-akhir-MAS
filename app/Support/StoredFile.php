<?php

namespace App\Support;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Utilitas berkas pada disk "public" (storage/app/public).
 *
 * Seluruh berkas diakses lewat route terautentikasi (FileController), bukan lewat
 * tautan simbolik public/storage, sehingga tidak bergantung pada `storage:link`
 * dan berkas pribadi siswa tidak terbuka untuk umum.
 */
class StoredFile
{
    public const DISK = 'public';

    /** Ekstensi yang boleh ditampilkan inline di browser. */
    private const INLINE_MIME = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
    ];

    public static function exists(?string $path): bool
    {
        return filled($path)
            && ! str_contains($path, '..')
            && Storage::disk(self::DISK)->exists($path);
    }

    public static function mimeFor(?string $path): ?string
    {
        return $path ? (self::INLINE_MIME[strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? null) : null;
    }

    public static function isImage(?string $path): bool
    {
        return str_starts_with((string) self::mimeFor($path), 'image/');
    }

    public static function isPdf(?string $path): bool
    {
        return self::mimeFor($path) === 'application/pdf';
    }

    /**
     * Hapus berkas hanya bila tidak ada record lain yang masih memakainya.
     * (Contoh: dokumen resmi hasil TTE memakai berkas yang sama dengan lampiran permohonan.)
     */
    public static function deleteIfUnused(?string $path, array $ignore = []): void
    {
        if (! filled($path) || str_contains($path, '..')) {
            return;
        }

        $ignoreModel = fn (string $class) => collect($ignore)
            ->first(fn ($m) => $m instanceof $class)?->getKey();

        $used = User::where('avatar_path', $path)
                ->when($ignoreModel(User::class), fn ($q, $id) => $q->where('id', '!=', $id))->exists()
            || DocumentRequest::where(fn ($q) => $q->where('support_file_path', $path)->orWhere('additional_file_path', $path))
                ->when($ignoreModel(DocumentRequest::class), fn ($q, $id) => $q->where('id', '!=', $id))->exists()
            || Document::where('file_path', $path)
                ->when($ignoreModel(Document::class), fn ($q, $id) => $q->where('id', '!=', $id))->exists();

        if (! $used) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
