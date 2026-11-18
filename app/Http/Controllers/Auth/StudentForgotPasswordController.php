<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Siswa yang lupa kata sandi mengajukan permintaan atur ulang.
 * Respons selalu sama agar orang lain tidak bisa menebak NISN yang terdaftar.
 */
class StudentForgotPasswordController extends Controller
{
    public function create()
    {
        return view('auth.lupa-sandi');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nisn' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $student = Student::with('user')->where('nisn', trim($data['nisn']))->first();

        if ($student && $student->user && $this->same($student->name, $data['name'])) {
            $existing = PasswordResetRequest::where('user_id', $student->user_id)->where('status', 'pending')->first();

            if ($existing) {
                $existing->touch();
            } else {
                PasswordResetRequest::create([
                    'user_id' => $student->user_id,
                    'student_id' => $student->id,
                    'note' => $data['note'] ?? null,
                ]);
            }
        }

        return redirect()->route('forgot.siswa')->with('sent', true);
    }

    private function same(string $a, string $b): bool
    {
        $norm = fn ($s) => mb_strtolower(preg_replace('/\s+/', ' ', trim($s)));

        return $norm($a) === $norm($b);
    }
}
