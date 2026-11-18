<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use Illuminate\Http\Request;

class PermohonanController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentRequest::with(['student', 'documentType'])
            ->whereIn('stage', ['diajukan', 'revisi', 'menunggu_tte']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('student', fn ($qq) => $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%"));
            });
        }

        if ($stage = $request->input('stage')) {
            $query->where('stage', $stage);
        }

        $queue = (clone $query)->orderBy('created_at')->paginate(5)->withQueryString();

        $selectedId = $request->input('selected', optional($queue->first())->id);
        $selected = $selectedId
            ? DocumentRequest::with(['student', 'documentType'])->find($selectedId)
            : null;

        return view('permohonan.index', [
            'queue' => $queue,
            'selected' => $selected,
            'stats' => [
                'menunggu_verifikasi' => DocumentRequest::where('stage', 'diajukan')->count(),
                'perlu_revisi' => DocumentRequest::where('stage', 'revisi')->count(),
                'menunggu_tte' => DocumentRequest::where('stage', 'menunggu_tte')->count(),
                'selesai_hari_ini' => DocumentRequest::where('stage', 'selesai')
                    ->whereDate('signed_at', now()->toDateString())->count(),
            ],
        ]);
    }

    /**
     * TU menyetujui berkas dan meneruskan ke Kepala Sekolah untuk TTE.
     */
    public function approve(Request $request, DocumentRequest $documentRequest)
    {
        abort_unless(in_array($documentRequest->stage, ['diajukan', 'revisi']), 422);

        $documentRequest->update([
            'stage' => 'menunggu_tte',
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'tu_notes' => null,
        ]);

        return back()->with('success', "{$documentRequest->ticket_number} disetujui dan diteruskan ke Kepala Sekolah untuk tanda tangan digital.");
    }

    public function requestRevision(Request $request, DocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'tu_notes' => ['required', 'string', 'max:500'],
        ]);

        abort_unless(in_array($documentRequest->stage, ['diajukan']), 422);

        $documentRequest->update([
            'stage' => 'revisi',
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'tu_notes' => $validated['tu_notes'],
        ]);

        return back()->with('success', "Permintaan revisi untuk {$documentRequest->ticket_number} telah dikirim ke siswa.");
    }

    public function reject(Request $request, DocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'tu_notes' => ['required', 'string', 'max:500'],
        ]);

        $documentRequest->update([
            'stage' => 'ditolak',
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'tu_notes' => $validated['tu_notes'],
        ]);

        return back()->with('success', "Permohonan {$documentRequest->ticket_number} ditolak.");
    }

    /**
     * Simulasi TTE Kepala Sekolah: membuat hash SHA-256 & menerbitkan dokumen
     * resmi nyata ke tabel documents (bukan mockup).
     *
     * Catatan: sistem ini belum memiliki role terpisah "kepala_sekolah",
     * jadi aksi tanda tangan tetap dijalankan oleh akun admin/petugas yang
     * berwenang. Ini disebutkan sebagai penyederhanaan pada laporan akhir.
     */
    public function sign(Request $request, DocumentRequest $documentRequest)
    {
        abort_unless($documentRequest->stage === 'menunggu_tte', 422);

        $documentRequest->issueDocument($request->user()->id);

        return back()->with('success', "{$documentRequest->ticket_number} berhasil ditandatangani secara digital dan dokumen resmi telah terbit.");
    }
}
