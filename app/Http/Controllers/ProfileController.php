<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** Kunci notifikasi yang dapat diatur, per peran. */
    public const NOTIFS = [
        'siswa' => ['revisi' => 'Pengajuan saya perlu revisi', 'selesai' => 'Pengajuan saya selesai dan siap diunduh'],
        'staff' => ['antrean' => 'Pengajuan baru yang menunggu tindakan', 'sandi' => 'Permintaan atur ulang kata sandi siswa'],
    ];

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'student' => $user->student,
            'notifOptions' => self::NOTIFS[$user->isSiswa() ? 'siswa' : 'staff'],
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);
        $user->phone = $data['phone'] ?? null;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $oldAvatar = $user->avatar_path;

        if ($request->hasFile('avatar')) {
            // Nama berkas dibuat Laravel (acak), bukan nama dari pengguna
            $stored = $request->file('avatar')->store('avatars', 'public');
            if ($stored === false) {
                return Redirect::route('profile.edit')
                    ->withInput()
                    ->withErrors(['avatar' => 'Foto gagal disimpan di server. Silakan coba lagi.']);
            }
            $user->avatar_path = $stored;
        } elseif ($request->boolean('remove_avatar')) {
            $user->avatar_path = null;
        }

        $user->save();

        // Foto lama dihapus setelah data baru tersimpan, dan hanya bila tidak dipakai record lain
        if ($oldAvatar && $oldAvatar !== $user->avatar_path) {
            \App\Support\StoredFile::deleteIfUnused($oldAvatar);
        }

        // Nomor telepon siswa juga disimpan pada data siswa agar TU dapat menghubungi
        if ($user->isSiswa() && $user->student) {
            $user->student->update(['phone' => $user->phone]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $user = $request->user();
        $keys = array_keys(self::NOTIFS[$user->isSiswa() ? 'siswa' : 'staff']);

        $prefs = $user->preferences ?? [];
        foreach ($keys as $key) {
            $prefs['notif'][$key] = $request->boolean('notif.'.$key);
        }
        $user->preferences = $prefs;
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'notif-updated');
    }
}
