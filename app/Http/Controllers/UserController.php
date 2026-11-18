<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Manajemen akun login (khusus admin): tambah, ubah, atur ulang kata sandi, hapus.
 */
class UserController extends Controller
{
    private const ROLES = ['admin' => 'Administrator', 'petugas' => 'Petugas TU', 'siswa' => 'Siswa'];

    public function index(Request $request)
    {
        $query = User::with('student')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        return view('users.index', ['users' => $query->paginate(10)->withQueryString(), 'roles' => self::ROLES]);
    }

    public function create()
    {
        return view('users.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['required', 'string', 'min:8'],
            'student_id' => ['nullable', 'exists:students,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'],
        ]);

        $this->linkStudent($user, $data['student_id'] ?? null);

        return redirect()->route('users.index')->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    public function edit(User $user)
    {
        return view('users.edit', $this->formData($user));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['nullable', 'string', 'min:8'],
            'student_id' => ['nullable', 'exists:students,id'],
        ]);

        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Anda tidak bisa menurunkan peran akun Anda sendiri.'])->withInput();
        }

        $user->fill(['name' => $data['name'], 'email' => $data['email'], 'role' => $data['role']]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        $this->linkStudent($user, $data['student_id'] ?? null);

        return redirect()->route('users.index')->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Akun yang sedang Anda pakai tidak bisa dihapus.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "Akun {$name} berhasil dihapus.");
    }

    private function formData(?User $user = null): array
    {
        return [
            'user' => $user,
            'roles' => self::ROLES,
            'students' => Student::where(fn ($q) => $q->whereNull('user_id')->when($user, fn ($qq) => $qq->orWhere('user_id', $user->id)))
                ->orderBy('name')->get(),
        ];
    }

    /** Hubungkan akun siswa ke data siswa (hanya untuk role siswa). */
    private function linkStudent(User $user, $studentId): void
    {
        Student::where('user_id', $user->id)->update(['user_id' => null]);

        if ($user->role === 'siswa' && $studentId) {
            Student::where('id', $studentId)->whereNull('user_id')->update(['user_id' => $user->id]);
        }
    }
}
