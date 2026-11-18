<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $academicYear = $request->input('academic_year');

        $baseQuery = Document::query();
        if ($academicYear) {
            $baseQuery->where('academic_year', $academicYear);
        }

        $recapByType = DocumentType::withCount(['documents' => function ($q) use ($academicYear) {
            if ($academicYear) {
                $q->where('academic_year', $academicYear);
            }
        }])->orderBy('name')->get();

        $recapByStatus = [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'verified' => (clone $baseQuery)->where('status', 'verified')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $recapByClass = Document::join('students', 'students.id', '=', 'documents.student_id')
            ->when($academicYear, fn ($q) => $q->where('documents.academic_year', $academicYear))
            ->select('students.class', DB::raw('count(*) as total'))
            ->groupBy('students.class')
            ->orderBy('students.class')
            ->pluck('total', 'class');

        $academicYears = Document::whereNotNull('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        $totalDocuments = (clone $baseQuery)->count();

        $requestStats = DocumentRequest::select('stage', DB::raw('count(*) as total'))
            ->groupBy('stage')
            ->pluck('total', 'stage');

        return view('reports.index', compact(
            'recapByType', 'recapByStatus', 'recapByClass', 'academicYears', 'academicYear', 'totalDocuments', 'requestStats'
        ));
    }

    public function export(Request $request)
    {
        $academicYear = $request->input('academic_year');

        $documents = Document::with(['student', 'documentType'])
            ->when($academicYear, fn ($q) => $q->where('academic_year', $academicYear))
            ->latest()
            ->get();

        $filename = 'laporan-dokumen-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($documents) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NISN', 'Nama Siswa', 'Kelas', 'Jenis Dokumen', 'Nomor Dokumen', 'Tanggal Upload', 'Status', 'Tahun Ajaran']);

            foreach ($documents as $doc) {
                fputcsv($file, [
                    $doc->student->nisn,
                    $doc->student->name,
                    $doc->student->class,
                    $doc->documentType->name,
                    $doc->document_number,
                    $doc->created_at->format('d-m-Y'),
                    $doc->status,
                    $doc->academic_year,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
