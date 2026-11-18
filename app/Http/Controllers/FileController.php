<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Support\StoredFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyajikan berkas (lampiran siswa, dokumen resmi, foto profil) lewat route
 * terautentikasi dengan otorisasi per record. Tidak memerlukan `storage:link`.
 */
class FileController extends Controller
{
    /** Lampiran permohonan: staf TU/admin atau siswa pemilik permohonan. */
    public function requestFile(Request $request, DocumentRequest $documentRequest, string $kind): Response
    {
        $user = $request->user();
        abort_unless(
            $user->isPetugasTu() || ($user->isSiswa() && $documentRequest->student?->user_id === $user->id),
            403
        );

        $path = match ($kind) {
            'support' => $documentRequest->support_file_path,
            'additional' => $documentRequest->additional_file_path,
            default => abort(404),
        };

        return $this->serve($request, $path);
    }

    /** Dokumen resmi: staf TU/admin atau siswa pemilik dokumen. */
    public function document(Request $request, Document $document): Response
    {
        $user = $request->user();
        abort_unless(
            $user->isPetugasTu() || ($user->isSiswa() && $document->student?->user_id === $user->id),
            403
        );

        return $this->serve($request, $document->file_path);
    }

    /** Foto profil: dapat dilihat seluruh pengguna yang sudah login (navbar, daftar, dsb). */
    public function avatar(Request $request, User $user): Response
    {
        abort_unless(StoredFile::exists($user->avatar_path) && StoredFile::isImage($user->avatar_path), 404);

        $response = $this->serve($request, $user->avatar_path);
        // Nama berkas unik per unggahan dan URL memuat ?v=<hash>, jadi aman di-cache di browser.
        $response->headers->set('Cache-Control', 'private, max-age=86400');

        return $response;
    }

    private function serve(Request $request, ?string $path): Response
    {
        abort_unless(StoredFile::exists($path), 404, 'Berkas tidak ditemukan di penyimpanan.');

        $mime = StoredFile::mimeFor($path);
        $disk = Storage::disk(StoredFile::DISK);
        $name = basename($path);

        // Format tak dikenal, atau ?unduh=1 -> unduh sebagai lampiran (tidak dirender inline).
        if (! $mime || $request->boolean('unduh')) {
            return $disk->download($path, $name, ['X-Content-Type-Options' => 'nosniff']);
        }

        return $disk->response($path, $name, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$name.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }
}
