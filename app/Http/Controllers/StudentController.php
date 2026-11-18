<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $students = $query->orderBy('name')->paginate(10)->withQueryString();

        $counts = [
            'total' => Student::count(),
            'aktif' => Student::where('status', 'aktif')->count(),
            'lulus' => Student::where('status', 'lulus')->count(),
            'pindah' => Student::where('status', 'pindah')->count(),
        ];

        return view('students.index', compact('students', 'counts'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn' => ['required', 'string', 'max:15', 'unique:students,nisn'],
            'name' => ['required', 'string', 'max:255'],
            'class' => ['nullable', 'string', 'max:50'],
            'entry_year' => ['nullable', 'string', 'max:9'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:aktif,lulus,pindah'],
        ]);

        $student = Student::create($validated);

        if ($request->boolean('create_login')) {
            $this->createLoginForStudent($student);
        }

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    protected function createLoginForStudent(Student $student): void
    {
        $email = $student->nisn.'@siswa.mas.sch.id';

        if (User::where('email', $email)->exists()) {
            return;
        }

        $user = User::create([
            'name' => $student->name,
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);

        $student->update(['user_id' => $user->id]);
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'nisn' => ['required', 'string', 'max:15', 'unique:students,nisn,'.$student->id],
            'name' => ['required', 'string', 'max:255'],
            'class' => ['nullable', 'string', 'max:50'],
            'entry_year' => ['nullable', 'string', 'max:9'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:aktif,lulus,pindah'],
        ]);

        $student->update($validated);

        if ($request->boolean('create_login') && ! $student->user_id) {
            $this->createLoginForStudent($student);
        }

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $q = $request->input('q');

        $students = Student::where('nisn', 'like', "%{$q}%")
            ->orWhere('name', 'like', "%{$q}%")
            ->limit(10)
            ->get(['id', 'nisn', 'name']);

        return response()->json($students);
    }
}
