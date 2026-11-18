<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentRequestController extends Controller
{
    public function create(Request $request)
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Akun ini belum dihubungkan ke data siswa. Hubungi Tata Usaha.');

        $documentTypes = DocumentType::orderBy('name')->get();

        return view('portal.requests.create', compact('documentTypes', 'student'));
    }

    public function store(Request $request)
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Akun ini belum dihubungkan ke data siswa. Hubungi Tata Usaha.');

        $validated = $request->validate([
            'document_type_id' => ['required', 'exists:document_types,id'],
            'service_name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'physical_copies' => ['required', 'integer', 'min:0', 'max:5'],
            'digital_copy' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:250'],
            'support_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'additional_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'agree' => ['accepted'],
        ], [
            'agree.accepted' => 'Anda harus menyetujui pernyataan kebenaran data sebelum mengajukan.',
        ]);

        $supportPath = $request->file('support_file')->store('requests/support', 'public');
        $additionalPath = $request->hasFile('additional_file')
            ? $request->file('additional_file')->store('requests/additional', 'public')
            : null;

        if ($supportPath === false || ($request->hasFile('additional_file') && $additionalPath === false)) {
            \App\Support\StoredFile::deleteIfUnused($supportPath ?: null);
            \App\Support\StoredFile::deleteIfUnused($additionalPath ?: null);

            return back()->withInput()->withErrors(['support_file' => 'Berkas gagal disimpan di server. Silakan coba lagi atau hubungi Tata Usaha.']);
        }

        $documentRequest = DocumentRequest::create([
            'ticket_number' => DocumentRequest::generateTicketNumber(),
            'student_id' => $student->id,
            'document_type_id' => $validated['document_type_id'],
            'service_name' => $validated['service_name'],
            'purpose' => $validated['purpose'],
            'physical_copies' => $validated['physical_copies'],
            'digital_copy' => $request->boolean('digital_copy'),
            'notes' => $validated['notes'] ?? null,
            'support_file_path' => $supportPath,
            'additional_file_path' => $additionalPath,
            'stage' => 'diajukan',
            'sla_due_at' => now()->addWeekdays((int) \App\Models\Setting::get('sla_days')),
        ]);

        return redirect()->route('portal.requests.index')
            ->with('success', "Permohonan {$documentRequest->ticket_number} berhasil diajukan dan masuk antrean verifikasi Tata Usaha.");
    }

    public function index(Request $request)
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Akun ini belum dihubungkan ke data siswa. Hubungi Tata Usaha.');

        $requests = $student->documentRequests()->with('documentType')->latest()->paginate(10);

        return view('portal.requests.index', compact('requests'));
    }

    /**
     * Siswa mengunggah ulang berkas ketika status "revisi" (tombol "Perbaiki Berkas").
     */
    public function resubmit(Request $request, DocumentRequest $documentRequest)
    {
        abort_unless($documentRequest->student_id === $request->user()->student?->id, 403);
        abort_unless($documentRequest->stage === 'revisi', 422, 'Permohonan ini tidak sedang dalam status revisi.');

        $validated = $request->validate([
            'support_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $oldPath = $documentRequest->support_file_path;
        $newPath = $request->file('support_file')->store('requests/support', 'public');
        abort_if($newPath === false, 500, 'Berkas gagal disimpan. Silakan coba lagi.');

        $documentRequest->update([
            'support_file_path' => $newPath,
            'stage' => 'diajukan',
            'tu_notes' => null,
        ]);

        // Hapus berkas lama hanya setelah data baru tersimpan dan bila tidak dipakai record lain
        \App\Support\StoredFile::deleteIfUnused($oldPath);

        return redirect()->route('portal.requests.index')
            ->with('success', 'Berkas perbaikan berhasil dikirim ulang ke Tata Usaha.');
    }
}
