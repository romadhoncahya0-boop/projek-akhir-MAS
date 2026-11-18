<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        abort_if(! $student, 403, 'Akun ini belum dihubungkan ke data siswa. Hubungi Tata Usaha.');

        $documents = $student->documents()->with('documentType')->latest('issue_date')->get();
        $requests = $student->documentRequests()->with('documentType')->latest()->take(3)->get();

        return view('portal.dashboard', [
            'student' => $student,
            'totalDocuments' => $documents->count(),
            'activeRequests' => $student->documentRequests()->whereIn('stage', ['diajukan', 'menunggu_tte'])->count(),
            'needsRevision' => $student->documentRequests()->where('stage', 'revisi')->count(),
            'completedRequests' => $student->documentRequests()->where('stage', 'selesai')->count(),
            'recentRequests' => $requests,
            'quickDownloads' => $documents->where('status', 'verified')->take(3),
        ]);
    }
}
