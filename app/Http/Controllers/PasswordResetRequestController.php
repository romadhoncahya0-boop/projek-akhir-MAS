<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;

/**
 * Tata Usaha / Admin memproses permintaan atur ulang kata sandi siswa.
 */
class PasswordResetRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = PasswordResetRequest::with(['student', 'handler'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()->paginate(10)->withQueryString();

        return view('reset-requests.index', ['requests' => $requests, 'status' => $status]);
    }

    /** Membuat kata sandi sementara; hanya ditampilkan sekali kepada petugas. */
    public function reset(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        abort_unless($passwordResetRequest->status === 'pending', 422);

        $temp = $this->tempPassword();
        $user = $passwordResetRequest->user;
        $user->password = $temp;
        $user->must_change_password = true;
        $user->save();

        $passwordResetRequest->update([
            'status' => 'done', 'handled_by' => $request->user()->id, 'handled_at' => now(),
        ]);

        return back()->with('temp_password', ['name' => $passwordResetRequest->student->name, 'password' => $temp]);
    }

    public function dismiss(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        abort_unless($passwordResetRequest->status === 'pending', 422);

        $passwordResetRequest->update([
            'status' => 'dismissed', 'handled_by' => $request->user()->id, 'handled_at' => now(),
        ]);

        return back()->with('success', 'Permintaan ditutup tanpa mengubah kata sandi.');
    }

    private function tempPassword(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $out = '';
        for ($i = 0; $i < 8; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $out;
    }
}
