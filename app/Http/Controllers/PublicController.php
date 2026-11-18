<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Student;

class PublicController extends Controller
{
    public function home()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // Angka nyata dari database (bukan dummy)
        return view('welcome', [
            'totalStudents' => Student::where('status', 'aktif')->count(),
            'totalDocuments' => Document::where('status', 'verified')->count(),
            'totalDone' => DocumentRequest::where('stage', 'selesai')->count(),
        ]);
    }
}
