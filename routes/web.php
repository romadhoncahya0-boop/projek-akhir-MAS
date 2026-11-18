<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\Portal\DocumentRequestController;
use App\Http\Controllers\Portal\StudentDocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\PublicController::class, 'home'])->name('home');

// Lupa kata sandi untuk siswa (diproses Tata Usaha)
Route::middleware('guest')->group(function () {
    Route::get('/lupa-kata-sandi', [\App\Http\Controllers\Auth\StudentForgotPasswordController::class, 'create'])->name('forgot.siswa');
    Route::post('/lupa-kata-sandi', [\App\Http\Controllers\Auth\StudentForgotPasswordController::class, 'store'])
        ->middleware('throttle:5,1')->name('forgot.siswa.store');
});

// Wajib mengganti kata sandi sementara
Route::middleware('auth')->group(function () {
    Route::get('/ganti-kata-sandi', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])->name('password.force.show');
    Route::post('/ganti-kata-sandi', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])->name('password.force.update');
});

Route::middleware(['auth', 'force.password'])->group(function () {
    // Dashboard bercabang otomatis sesuai role (lihat DashboardController)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Berkas terproteksi (lampiran siswa, dokumen resmi, foto profil) - otorisasi per record
    Route::get('/berkas/permohonan/{documentRequest}/{kind}', [FileController::class, 'requestFile'])
        ->whereIn('kind', ['support', 'additional'])->name('files.request');
    Route::get('/berkas/dokumen/{document}', [FileController::class, 'document'])->name('files.document');
    Route::get('/foto-profil/{user}', [FileController::class, 'avatar'])->name('files.avatar');

    Route::get('/cari', \App\Http\Controllers\SearchController::class)->name('search');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/notifikasi', [ProfileController::class, 'updateNotifications'])->name('profile.notifications');

    // ===== Portal Siswa (role: siswa) =====
    Route::middleware('role:siswa')->group(function () {
        Route::get('/dokumen-saya', [StudentDocumentController::class, 'index'])->name('portal.documents');

        Route::get('/ajukan-dokumen', [DocumentRequestController::class, 'create'])->name('portal.requests.create');
        Route::post('/ajukan-dokumen', [DocumentRequestController::class, 'store'])->name('portal.requests.store');

        Route::get('/riwayat-pengajuan', [DocumentRequestController::class, 'index'])->name('portal.requests.index');
        Route::post('/riwayat-pengajuan/{documentRequest}/perbaiki', [DocumentRequestController::class, 'resubmit'])
            ->name('portal.requests.resubmit');
    });

    // ===== Manajemen Pengguna (khusus role: admin) =====
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
        Route::get('/pengaturan-sekolah', [\App\Http\Controllers\SchoolSettingController::class, 'edit'])->name('school-settings.edit');
        Route::put('/pengaturan-sekolah', [\App\Http\Controllers\SchoolSettingController::class, 'update'])->name('school-settings.update');
    });

    // ===== Administrasi / Tata Usaha (role: admin, petugas) =====
    Route::middleware('role:admin,petugas')->group(function () {
        Route::get('/permohonan', [PermohonanController::class, 'index'])->name('permohonan.index');
        Route::post('/permohonan/{documentRequest}/setujui', [PermohonanController::class, 'approve'])->name('permohonan.approve');
        Route::post('/permohonan/{documentRequest}/revisi', [PermohonanController::class, 'requestRevision'])->name('permohonan.revisi');
        Route::post('/permohonan/{documentRequest}/tolak', [PermohonanController::class, 'reject'])->name('permohonan.reject');
        Route::post('/permohonan/{documentRequest}/tanda-tangan', [PermohonanController::class, 'sign'])->name('permohonan.sign');

        Route::get('/permintaan-sandi', [\App\Http\Controllers\PasswordResetRequestController::class, 'index'])->name('reset-requests.index');
        Route::post('/permintaan-sandi/{passwordResetRequest}/atur-ulang', [\App\Http\Controllers\PasswordResetRequestController::class, 'reset'])->name('reset-requests.reset');
        Route::post('/permintaan-sandi/{passwordResetRequest}/tutup', [\App\Http\Controllers\PasswordResetRequestController::class, 'dismiss'])->name('reset-requests.dismiss');

        Route::post('/documents/{document}/verify', [DocumentController::class, 'verify'])->name('documents.verify');
        Route::post('/documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
        Route::resource('documents', DocumentController::class)->except(['show']);

        // PENTING: route search harus didaftarkan SEBELUM Route::resource('students', ...)
        // supaya '/students/search' tidak tertangkap sebagai '/students/{student}'
        Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
        Route::resource('students', StudentController::class)->except(['show']);

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
});

require __DIR__.'/auth.php';
