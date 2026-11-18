<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Menjaga agar halaman admin (Manajemen Permohonan, Data Siswa, dll)
     * tidak bisa diakses oleh akun siswa, dan sebaliknya halaman Portal Siswa
     * (Ajukan Dokumen, Dokumen Saya) tidak bisa diakses oleh TU/admin.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
