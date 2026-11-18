<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    private function admin(): User { return User::where('role', 'admin')->first(); }
    private function siswa(): User { return User::where('role', 'siswa')->first(); }

    public function test_public_pages(): void
    {
        $this->get('/')->assertOk()->assertSee('MAS');
        $this->get('/login')->assertOk();
    }

    public function test_admin_pages(): void
    {
        $a = $this->admin();
        foreach (['/dashboard', '/permohonan', '/documents', '/documents/create', '/students', '/students/create', '/reports', '/profile', '/cari?q=Rizky'] as $url) {
            $this->actingAs($a)->get($url)->assertOk();
        }
        $this->actingAs($a)->get('/permohonan?selected=1')->assertOk();
        $this->actingAs($a)->get('/students/search?q=Rizky')->assertOk();
    }

    public function test_student_pages(): void
    {
        $s = $this->siswa();
        foreach (['/dashboard', '/dokumen-saya', '/dokumen-saya?category=Surat%20Keterangan', '/ajukan-dokumen', '/riwayat-pengajuan', '/profile', '/cari?q=rapor'] as $url) {
            $this->actingAs($s)->get($url)->assertOk();
        }
    }

    public function test_role_isolation(): void
    {
        $this->actingAs($this->siswa())->get('/permohonan')->assertForbidden();
        $this->actingAs($this->siswa())->get('/students')->assertForbidden();
        $this->actingAs($this->siswa())->get('/documents')->assertForbidden();
        $this->actingAs($this->admin())->get('/dokumen-saya')->assertForbidden();
    }

    public function test_guest_redirected(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/permohonan')->assertRedirect('/login');
        $this->getJson('/cari?q=abc')->assertUnauthorized();
    }

    public function test_search_scoped_to_student(): void
    {
        $other = Student::where('name', 'Annisa Nurul Aini')->first();
        $json = $this->actingAs($this->siswa())->getJson('/cari?q=Annisa')->assertOk()->json();
        $this->assertStringNotContainsString('Annisa', json_encode($json));
    }

    public function test_request_workflow(): void
    {
        $s = $this->siswa();
        $type = \App\Models\DocumentType::first();

        $this->actingAs($s)->post('/ajukan-dokumen', [
            'document_type_id' => $type->id, 'service_name' => 'Uji', 'purpose' => 'Uji tujuan',
            'physical_copies' => 2, 'digital_copy' => 1, 'agree' => 1,
            'support_file' => UploadedFile::fake()->create('a.pdf', 100, 'application/pdf'),
        ])->assertRedirect('/riwayat-pengajuan')->assertSessionHasNoErrors();

        $req = DocumentRequest::latest('id')->first();
        $this->assertEquals('diajukan', $req->stage);

        $a = $this->admin();
        $this->actingAs($a)->post("/permohonan/{$req->id}/revisi", ['tu_notes' => 'Buram'])->assertSessionHasNoErrors();
        $this->assertEquals('revisi', $req->fresh()->stage);

        $this->actingAs($s)->post("/riwayat-pengajuan/{$req->id}/perbaiki", [
            'support_file' => UploadedFile::fake()->create('b.pdf', 50, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $this->assertEquals('diajukan', $req->fresh()->stage);

        $this->actingAs($a)->post("/permohonan/{$req->id}/setujui")->assertSessionHasNoErrors();
        $this->assertEquals('menunggu_tte', $req->fresh()->stage);

        $this->actingAs($a)->post("/permohonan/{$req->id}/tanda-tangan")->assertSessionHasNoErrors();
        $req = $req->fresh();
        $this->assertEquals('selesai', $req->stage);
        $this->assertNotNull($req->document_id);
        $this->assertNotEmpty($req->signed_hash);

        $this->actingAs($s)->get('/dokumen-saya')->assertOk()->assertSee($req->ticket_number, false);
    }

    public function test_student_cannot_touch_others_request(): void
    {
        $req = DocumentRequest::where('student_id', '!=', $this->siswa()->student->id)->where('stage', 'revisi')->first();
        $this->actingAs($this->siswa())->post("/riwayat-pengajuan/{$req->id}/perbaiki", [
            'support_file' => UploadedFile::fake()->create('c.pdf', 10, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_document_verify_reject_and_student_login_creation(): void
    {
        $a = $this->admin();
        $doc = Document::create([
            'student_id' => Student::first()->id, 'document_type_id' => 1, 'document_number' => 'X1',
            'file_path' => 'x.pdf', 'status' => 'pending', 'uploaded_by' => $a->id,
        ]);
        $this->actingAs($a)->post("/documents/{$doc->id}/verify")->assertSessionHasNoErrors();
        $this->assertEquals('verified', $doc->fresh()->status);
        $this->actingAs($a)->post("/documents/{$doc->id}/reject", ['notes' => 'x'])->assertSessionHasNoErrors();
        $this->assertEquals('rejected', $doc->fresh()->status);

        $this->actingAs($a)->post('/students', [
            'nisn' => '0099999999', 'name' => 'Uji Siswa', 'class' => 'X A', 'entry_year' => '2025/2026',
            'status' => 'aktif', 'create_login' => 1,
        ])->assertSessionHasNoErrors();
        $st = Student::where('nisn', '0099999999')->first();
        $this->assertNotNull($st->user_id);
        $this->assertSame('siswa', $st->user->role);
        $this->post('/logout');
        $this->post('/login', ['email' => '0099999999@siswa.mas.sch.id', 'password' => 'password'])->assertRedirect('/dashboard');
    }

    public function test_user_management_admin_only(): void
    {
        $a = $this->admin();
        $this->actingAs($a)->get('/users')->assertOk();
        $this->actingAs($a)->get('/users/create')->assertOk();

        $st = Student::whereNull('user_id')->first();
        $this->actingAs($a)->post('/users', ['name' => 'Budi TU', 'email' => 'budi@mas.sch.id', 'role' => 'petugas', 'password' => 'rahasia123'])->assertSessionHasNoErrors();
        $this->actingAs($a)->post('/users', ['name' => 'Siswa Baru', 'email' => 'sb@mas.sch.id', 'role' => 'siswa', 'password' => 'rahasia123', 'student_id' => $st->id])->assertSessionHasNoErrors();
        $this->assertNotNull($st->fresh()->user_id);
        $this->assertSame('siswa', User::where('email', 'sb@mas.sch.id')->first()->role);

        $budi = User::where('email', 'budi@mas.sch.id')->first();
        $this->actingAs($a)->get("/users/{$budi->id}/edit")->assertOk();
        $this->actingAs($a)->put("/users/{$budi->id}", ['name' => 'Budi', 'email' => 'budi@mas.sch.id', 'role' => 'petugas', 'password' => 'baru12345'])->assertSessionHasNoErrors();
        $this->actingAs($a)->put("/users/{$a->id}", ['name' => 'A', 'email' => $a->email, 'role' => 'petugas'])->assertSessionHasErrors('role');
        $this->actingAs($a)->delete("/users/{$a->id}")->assertSessionHas('error');
        $this->actingAs($a)->delete("/users/{$budi->id}")->assertSessionHasNoErrors();

        $this->post('/logout');
        $this->post('/login', ['email' => 'sb@mas.sch.id', 'password' => 'rahasia123'])->assertRedirect('/dashboard');
        $this->get('/users')->assertForbidden();
    }

    public function test_petugas_cannot_manage_users(): void
    {
        $p = User::create(['name' => 'P', 'email' => 'p@mas.sch.id', 'password' => 'password', 'role' => 'petugas']);
        $this->actingAs($p)->get('/users')->assertForbidden();
        $this->actingAs($p)->get('/permohonan')->assertOk();
    }

    public function test_edit_student_can_create_login(): void
    {
        $a = $this->admin();
        $st = Student::whereNull('user_id')->first();
        $this->actingAs($a)->put("/students/{$st->id}", ['nisn' => $st->nisn, 'name' => $st->name, 'status' => 'aktif', 'create_login' => 1])->assertSessionHasNoErrors();
        $this->assertNotNull($st->fresh()->user_id);
        $this->assertSame('siswa', $st->fresh()->user->role);
    }

    public function test_profile_cannot_escalate_role(): void
    {
        $s = $this->siswa();
        $this->actingAs($s)->patch('/profile', ['name' => 'X', 'email' => $s->email, 'role' => 'admin']);
        $this->assertSame('siswa', $s->fresh()->role);
    }

    public function test_forgot_password_request_flow(): void
    {
        $this->get('/lupa-kata-sandi')->assertOk();

        $this->post('/lupa-kata-sandi', ['nisn' => '0068912345', 'name' => 'muhammad  RIZKY pratama'])->assertRedirect('/lupa-kata-sandi')->assertSessionHas('sent');
        $this->post('/lupa-kata-sandi', ['nisn' => '0068912345', 'name' => 'Muhammad Rizky Pratama'])->assertSessionHas('sent');
        $this->assertSame(1, \App\Models\PasswordResetRequest::where('status', 'pending')->count());

        // data tidak cocok: respons sama, tidak ada permintaan baru
        $this->post('/lupa-kata-sandi', ['nisn' => '0000000000', 'name' => 'Siapa Saja'])->assertSessionHas('sent');
        $this->post('/lupa-kata-sandi', ['nisn' => '0068912346', 'name' => 'Nama Salah'])->assertSessionHas('sent');
        $this->assertSame(1, \App\Models\PasswordResetRequest::count());
    }

    public function test_staff_resets_password_and_student_must_change_it(): void
    {
        $this->post('/lupa-kata-sandi', ['nisn' => '0068912345', 'name' => 'Muhammad Rizky Pratama']);
        $req = \App\Models\PasswordResetRequest::first();
        $a = $this->admin();

        $this->actingAs($a)->get('/permintaan-sandi')->assertOk()->assertSee('Muhammad Rizky Pratama');
        $this->actingAs($this->siswa())->get('/permintaan-sandi')->assertForbidden();

        $this->actingAs($a)->post("/permintaan-sandi/{$req->id}/atur-ulang")->assertSessionHas('temp_password');
        $temp = session('temp_password')['password'];
        $this->assertSame(8, strlen($temp));
        $this->assertSame('done', $req->fresh()->status);
        $this->actingAs($a)->post("/permintaan-sandi/{$req->id}/atur-ulang")->assertStatus(422);

        $this->app['auth']->logout();
        $this->post('/login', ['email' => 'rizky.pratama@siswa.mas.sch.id', 'password' => 'password'])->assertSessionHasErrors();
        $this->post('/login', ['email' => 'rizky.pratama@siswa.mas.sch.id', 'password' => $temp])->assertRedirect('/dashboard');

        // wajib ganti sandi sebelum memakai fitur lain
        $this->get('/dashboard')->assertRedirect('/ganti-kata-sandi');
        $this->get('/dokumen-saya')->assertRedirect('/ganti-kata-sandi');
        $this->get('/ganti-kata-sandi')->assertOk();
        $this->post('/ganti-kata-sandi', ['password' => $temp, 'password_confirmation' => $temp])->assertSessionHasErrors('password');
        $this->post('/ganti-kata-sandi', ['password' => 'pendek', 'password_confirmation' => 'pendek'])->assertSessionHasErrors('password');
        $this->post('/ganti-kata-sandi', ['password' => 'SandiBaru123', 'password_confirmation' => 'SandiBaru123'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk();
        $this->assertFalse($this->siswa()->must_change_password);
    }

    public function test_dismiss_reset_request(): void
    {
        $this->post('/lupa-kata-sandi', ['nisn' => '0068912345', 'name' => 'Muhammad Rizky Pratama']);
        $req = \App\Models\PasswordResetRequest::first();
        $this->actingAs($this->admin())->post("/permintaan-sandi/{$req->id}/tutup")->assertSessionHasNoErrors();
        $this->assertSame('dismissed', $req->fresh()->status);
        $this->assertFalse($this->siswa()->must_change_password);
    }

    public function test_profile_update_with_avatar_and_phone(): void
    {
        $s = $this->siswa();
        $this->actingAs($s)->get('/profile')->assertOk()->assertSee('Data Akademik')->assertSee('0068912345');

        $this->actingAs($s)->patch('/profile', [
            'name' => $s->name, 'email' => $s->email, 'phone' => '0812 3456 7890',
            'avatar' => UploadedFile::fake()->createWithContent('foto.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')),
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');

        $s->refresh();
        $this->assertNotNull($s->avatar_path);
        Storage::disk('public')->assertExists($s->avatar_path);
        $this->assertSame('0812 3456 7890', $s->student->fresh()->phone);

        // foto terlihat di header
        $this->actingAs($s)->get('/dashboard')->assertSee('storage/'.$s->avatar_path, false);

        // validasi
        $this->actingAs($s)->patch('/profile', ['name' => $s->name, 'email' => $s->email, 'phone' => 'abc'])->assertSessionHasErrors('phone');
        $this->actingAs($s)->patch('/profile', ['name' => $s->name, 'email' => $s->email, 'avatar' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])->assertSessionHasErrors('avatar');

        // hapus foto
        $old = $s->avatar_path;
        $this->actingAs($s)->patch('/profile', ['name' => $s->name, 'email' => $s->email, 'remove_avatar' => 1])->assertSessionHasNoErrors();
        $this->assertNull($s->fresh()->avatar_path);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_notification_preferences_filter_bell(): void
    {
        $s = $this->siswa();
        DocumentRequest::where('student_id', $s->student->id)->update(['stage' => 'revisi', 'tu_notes' => 'x']);
        $this->actingAs($s)->get('/dashboard')->assertSee('REQ-2025-0841 perlu revisi', false);

        $this->actingAs($s)->patch('/profile/notifikasi', ['notif' => ['selesai' => 1]])->assertSessionHasNoErrors();
        $this->assertFalse($s->fresh()->wantsNotification('revisi'));
        $this->actingAs($s)->get('/dashboard')->assertDontSee('REQ-2025-0841 perlu revisi', false);

        \App\Models\PasswordResetRequest::create(['user_id' => $s->id, 'student_id' => $s->student->id]);
        $a = $this->admin();
        $this->actingAs($a)->get('/dashboard')->assertSee('meminta atur ulang kata sandi');
        $this->actingAs($a)->patch('/profile/notifikasi', ['notif' => ['antrean' => 1]]);
        $this->actingAs($a)->get('/dashboard')->assertDontSee('meminta atur ulang kata sandi');
    }

    public function test_school_settings_admin_only_and_applied(): void
    {
        $this->actingAs($this->siswa())->get('/pengaturan-sekolah')->assertForbidden();
        $p = User::create(['name' => 'P', 'email' => 'p2@mas.sch.id', 'password' => 'password', 'role' => 'petugas']);
        $this->actingAs($p)->get('/pengaturan-sekolah')->assertForbidden();

        $a = $this->admin();
        $this->actingAs($a)->get('/pengaturan-sekolah')->assertOk();
        $this->actingAs($a)->put('/pengaturan-sekolah', ['school_name' => 'SMK Contoh', 'school_address' => 'Jl. Mawar 1', 'contact_email' => 'tu@contoh.sch.id',
            'contact_phone' => '0271 123456', 'service_days' => 'Senin-Sabtu', 'service_hours' => '07.00-14.00 WIB', 'sla_days' => 5, 'announcement' => 'Libur semester dimulai 20 Desember.'])
            ->assertSessionHasNoErrors();
        $this->assertSame('SMK Contoh', \App\Models\Setting::get('school_name'));

        $this->actingAs($a)->put('/pengaturan-sekolah', ['school_name' => 'X', 'school_address' => 'Y', 'contact_email' => 'bukan-email', 'service_days' => 'a', 'service_hours' => 'b', 'sla_days' => 0])
            ->assertSessionHasErrors(['contact_email', 'sla_days']);

        $this->app['auth']->logout();
        $this->get('/')->assertSee('tu@contoh.sch.id')->assertSee('07.00-14.00 WIB');

        $s = $this->siswa();
        $this->actingAs($s)->get('/dashboard')->assertSee('Libur semester dimulai 20 Desember.');

        $this->actingAs($s)->post('/ajukan-dokumen', [
            'document_type_id' => \App\Models\DocumentType::first()->id, 'service_name' => 'Uji', 'purpose' => 'Uji', 'physical_copies' => 1, 'agree' => 1,
            'support_file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $req = DocumentRequest::latest('id')->first();
        $this->assertTrue($req->sla_due_at->isSameDay(now()->addWeekdays(5)));
    }

    public function test_login_time_is_recorded(): void
    {
        $this->post('/login', ['email' => 'admin@mas.sch.id', 'password' => 'password'])->assertRedirect('/dashboard');
        $a = $this->admin()->fresh();
        $this->assertNotNull($a->last_login_at);
        $this->assertNull($a->previous_login_at);

        $this->post('/logout');
        $this->post('/login', ['email' => 'admin@mas.sch.id', 'password' => 'password']);
        $this->assertNotNull($this->admin()->fresh()->previous_login_at);
        $this->get('/profile')->assertOk()->assertSee('Masuk sebelumnya');
    }
}
