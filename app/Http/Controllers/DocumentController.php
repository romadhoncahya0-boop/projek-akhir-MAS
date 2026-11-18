<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['student', 'documentType']);

        if ($search = $request->input('search')) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('document_type_id')) {
            $query->where('document_type_id', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $documents = $query->latest()->paginate(10)->withQueryString();
        $documentTypes = DocumentType::orderBy('name')->get();

        $counts = [
            'total' => Document::count(),
            'pending' => Document::where('status', 'pending')->count(),
            'verified' => Document::where('status', 'verified')->count(),
            'rejected' => Document::where('status', 'rejected')->count(),
        ];

        return view('documents.index', compact('documents', 'documentTypes', 'counts'));
    }

    public function create()
    {
        $documentTypes = DocumentType::orderBy('name')->get();

        return view('documents.create', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn' => ['required', 'exists:students,nisn'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'academic_year' => ['nullable', 'string', 'max:9'],
            'notes' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        $student = Student::where('nisn', $validated['nisn'])->firstOrFail();
        $path = $request->file('file')->store('documents', 'public');

        Document::create([
            'student_id' => $student->id,
            'document_type_id' => $validated['document_type_id'],
            'document_number' => $validated['document_number'] ?? null,
            'issue_date' => $validated['issue_date'] ?? null,
            'academic_year' => $validated['academic_year'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'file_path' => $path,
            'status' => 'pending',
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil diunggah dan menunggu verifikasi.');
    }

    public function edit(Document $document)
    {
        $documentTypes = DocumentType::orderBy('name')->get();

        return view('documents.edit', compact('document', 'documentTypes'));
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'document_type_id' => ['required', 'exists:document_types,id'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'academic_year' => ['nullable', 'string', 'max:9'],
            'notes' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->hasFile('file')) {
            $oldPath = $document->file_path;
            $validated['file_path'] = $request->file('file')->store('documents', 'public');
            $document->update($validated);

            \App\Support\StoredFile::deleteIfUnused($oldPath);
        } else {
            $document->update($validated);
        }

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document)
    {
        $path = $document->file_path;
        $document->delete();

        \App\Support\StoredFile::deleteIfUnused($path);

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function verify(Document $document)
    {
        $document->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Dokumen dinyatakan terverifikasi.');
    }

    public function reject(Request $request, Document $document)
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        $document->update([
            'status' => 'rejected',
            'notes' => $validated['notes'],
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Dokumen ditolak dan catatan alasan telah disimpan.');
    }
}
