<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun yang kata sandinya baru diatur ulang oleh Tata Usaha wajib
 * membuat kata sandi baru sebelum memakai fitur lain.
 */
class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs('password.force.*', 'logout')) {
            return redirect()->route('password.force.show');
        }

        return $next($request);
    }
}
