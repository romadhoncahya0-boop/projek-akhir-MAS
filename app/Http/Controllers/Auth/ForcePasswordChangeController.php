<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChangeController extends Controller
{
    public function show(Request $request)
    {
        if (! $request->user()->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.ganti-sandi');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();
        if (\Illuminate\Support\Facades\Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'Kata sandi baru tidak boleh sama dengan kata sandi sementara.']);
        }

        $user->password = $data['password'];
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Kata sandi baru berhasil disimpan. Selamat datang kembali.');
    }
}
