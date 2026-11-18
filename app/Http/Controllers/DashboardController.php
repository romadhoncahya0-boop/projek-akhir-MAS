<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        // Akun siswa punya dashboard sendiri (lihat Portal\StudentDashboardController)
        if (auth()->user()->role === 'siswa') {
            return app(\App\Http\Controllers\Portal\StudentDashboardController::class)->index(request());
        }

        $totalStudents = Student::where('status', 'aktif')->count();
        $totalDocuments = Document::count();
        $pendingDocuments = Document::where('status', 'pending')->count();
        $rejectedDocuments = Document::where('status', 'rejected')->count();

        $recentActivities = Document::with(['student', 'documentType'])
            ->latest()
            ->take(5)
            ->get();

        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $monthlyUploads = Document::selectRaw(($driver === 'sqlite' ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')") . " as month, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $requestStats = [
            'diajukan' => \App\Models\DocumentRequest::where('stage', 'diajukan')->count(),
            'revisi' => \App\Models\DocumentRequest::where('stage', 'revisi')->count(),
            'menunggu_tte' => \App\Models\DocumentRequest::where('stage', 'menunggu_tte')->count(),
            'selesai' => \App\Models\DocumentRequest::where('stage', 'selesai')->count(),
        ];
        $queue = \App\Models\DocumentRequest::with('student')->whereIn('stage', ['diajukan', 'menunggu_tte'])->oldest()->take(5)->get();

        return view('dashboard', compact(
            'requestStats',
            'queue',
                'totalStudents',
            'totalDocuments',
            'pendingDocuments',
            'rejectedDocuments',
            'recentActivities',
            'monthlyUploads'
        ));
    }
}
