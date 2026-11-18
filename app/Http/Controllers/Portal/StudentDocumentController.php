<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentDocumentController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Akun ini belum dihubungkan ke data siswa. Hubungi Tata Usaha.');

        $query = $student->documents()->with('documentType');

        if ($category = $request->input('category')) {
            $query->whereHas('documentType', fn ($q) => $q->where('category', $category));
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhereHas('documentType', fn ($qq) => $qq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($year = $request->input('academic_year')) {
            $query->where('academic_year', $year);
        }

        $documents = $query->latest('issue_date')->paginate(6)->withQueryString();

        $categories = [
            'Rapor & Transkrip', 'Surat Keterangan', 'Sertifikat & Piagam', 'Berkas Identitas',
        ];

        $counts = collect($categories)->mapWithKeys(fn ($cat) => [
            $cat => $student->documents()->whereHas('documentType', fn ($q) => $q->where('category', $cat))->count(),
        ]);

        return view('portal.documents.index', [
            'student' => $student,
            'documents' => $documents,
            'categories' => $categories,
            'counts' => $counts,
            'totalDocuments' => $student->documents()->count(),
            'activeCategory' => $category,
        ]);
    }
}
