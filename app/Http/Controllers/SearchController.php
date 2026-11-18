<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Pencarian global (Ctrl+K). Hasil dibatasi sesuai role di sisi server:
 * siswa hanya melihat data miliknya, staf melihat data sesuai kewenangannya.
 */
class SearchController extends Controller
{
    private const STAGES = [
        'diajukan' => 'Menunggu verifikasi',
        'revisi' => 'Perlu revisi',
        'menunggu_tte' => 'Menunggu tanda tangan',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];

    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['groups' => []]);
        }

        $user = $request->user();
        $like = '%'.$q.'%';
        $groups = [];

        if ($user->role === 'siswa') {
            $student = $user->student;
            if (! $student) {
                return response()->json(['groups' => []]);
            }

            $docs = $student->documents()->with('documentType')
                ->where(fn ($w) => $w->where('document_number', 'like', $like)
                    ->orWhereHas('documentType', fn ($t) => $t->where('name', 'like', $like)))
                ->latest()->take(5)->get()
                ->map(fn ($d) => ['title' => $d->documentType->name, 'sub' => $d->document_number ?: 'Tanpa nomor', 'url' => route('portal.documents', ['search' => $q])]);

            $reqs = $student->documentRequests()
                ->where(fn ($w) => $w->where('ticket_number', 'like', $like)->orWhere('service_name', 'like', $like)->orWhere('purpose', 'like', $like))
                ->latest()->take(5)->get()
                ->map(fn ($r) => ['title' => $r->service_name, 'sub' => $r->ticket_number.' · '.self::STAGES[$r->stage], 'url' => route('portal.requests.index')]);
        } else {
            $students = Student::where(fn ($w) => $w->where('name', 'like', $like)->orWhere('nisn', 'like', $like))
                ->take(5)->get()
                ->map(fn ($s) => ['title' => $s->name, 'sub' => 'NISN '.$s->nisn.' · '.$s->class, 'url' => route('students.index', ['search' => $s->nisn])]);
            if ($students->isNotEmpty()) {
                $groups[] = ['label' => 'Siswa', 'items' => $students->values()];
            }

            $docs = Document::with(['student', 'documentType'])
                ->where(fn ($w) => $w->where('document_number', 'like', $like)
                    ->orWhereHas('documentType', fn ($t) => $t->where('name', 'like', $like))
                    ->orWhereHas('student', fn ($s) => $s->where('name', 'like', $like)))
                ->latest()->take(5)->get()
                ->map(fn ($d) => ['title' => $d->documentType->name, 'sub' => $d->student->name.' · '.($d->document_number ?: 'Tanpa nomor'), 'url' => route('documents.index', ['search' => $q])]);

            $statusMatch = collect(self::STAGES)->filter(fn ($label) => stripos($label, $q) !== false)->keys();
            $reqs = DocumentRequest::with('student')
                ->where(fn ($w) => $w->where('ticket_number', 'like', $like)
                    ->orWhere('service_name', 'like', $like)
                    ->orWhereHas('student', fn ($s) => $s->where('name', 'like', $like))
                    ->orWhereIn('stage', $statusMatch))
                ->latest()->take(5)->get()
                ->map(fn ($r) => ['title' => $r->service_name, 'sub' => $r->ticket_number.' · '.$r->student->name.' · '.self::STAGES[$r->stage], 'url' => route('permohonan.index', ['selected' => $r->id])]);
        }

        if ($docs->isNotEmpty()) {
            $groups[] = ['label' => 'Dokumen', 'items' => $docs->values()];
        }
        if ($reqs->isNotEmpty()) {
            $groups[] = ['label' => 'Pengajuan', 'items' => $reqs->values()];
        }

        return response()->json(['groups' => $groups]);
    }
}
