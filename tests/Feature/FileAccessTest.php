<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Lampiran siswa -> admin, dokumen resmi, dan foto profil (alur upload, penyimpanan, tampil).
 */
class FileAccessTest extends TestCase
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

    /** PNG 1x1 valid tanpa memerlukan ekstensi GD. */
    private function png(string $name = 'foto.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
        );
    }

    private function submitAs(User $siswa, $file): DocumentRequest
    {
        $this->actingAs($siswa)->post('/ajukan-dokumen', [
            'document_type_id' => DocumentType::first()->id, 'service_name' => 'Uji', 'purpose' => 'Uji',
            'physical_copies' => 1, 'digital_copy' => 1, 'agree' => 1, 'support_file' => $file,
        ])->assertSessionHasNoErrors();

        return DocumentRequest::latest('id')->first();
    }

    public function test_student_image_is_visible_to_admin_with_thumbnail_and_stream(): void
    {
        $req = $this->submitAs($this->siswa(), $this->png('bukti.png'));

        Storage::disk('public')->assertExists($req->support_file_path);

        $page = $this->actingAs($this->admin())->get('/permohonan?selected='.$req->id)->assertOk();
        $page->assertSee($req->fileUrl('support'), false);
        $page->assertDontSee('/storage/'.$req->support_file_path, false);

        $this->actingAs($this->admin())->get($req->fileUrl('support'))
            ->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_attachment_authorization(): void
    {
        $req = $this->submitAs($this->siswa(), $this->png('a.jpg'));
        $url = $req->fileUrl('support');

        auth()->logout();
        $this->get($url)->assertRedirect('/login');

        $otherUser = User::where('role', 'siswa')->where('id', '!=', $this->siswa()->id)->first();
        if ($otherUser) {
            $this->actingAs($otherUser)->get($url)->assertForbidden();
        }
        $this->actingAs($this->siswa())->get($url)->assertOk();
        $this->actingAs($this->admin())->get($url)->assertOk();
    }

    public function test_two_requests_show_their_own_files(): void
    {
        $a = $this->submitAs($this->siswa(), $this->png('a.png'));
        $b = $this->submitAs($this->siswa(), $this->png('b.png'));

        $this->assertNotEquals($a->support_file_path, $b->support_file_path);
        $this->assertNotEquals($a->fileUrl('support'), $b->fileUrl('support'));
    }

    public function test_missing_file_gives_404_and_informative_admin_message(): void
    {
        $req = $this->submitAs($this->siswa(), $this->png('a.png'));
        Storage::disk('public')->delete($req->support_file_path);

        $this->actingAs($this->admin())->get($req->fileUrl('support'))->assertNotFound();
        $this->actingAs($this->admin())->get('/permohonan?selected='.$req->id)
            ->assertOk()->assertSee('Berkas tidak ditemukan di penyimpanan server');
    }

    public function test_invalid_attachment_is_rejected(): void
    {
        $this->actingAs($this->siswa())->post('/ajukan-dokumen', [
            'document_type_id' => DocumentType::first()->id, 'service_name' => 'Uji', 'purpose' => 'Uji',
            'physical_copies' => 1, 'agree' => 1,
            'support_file' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('support_file');

        $this->actingAs($this->siswa())->post('/ajukan-dokumen', [
            'document_type_id' => DocumentType::first()->id, 'service_name' => 'Uji', 'purpose' => 'Uji',
            'physical_copies' => 1, 'agree' => 1,
            'support_file' => UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf'),
        ])->assertSessionHasErrors('support_file');
    }

    public function test_deleting_signed_document_keeps_students_original_attachment(): void
    {
        $siswa = $this->siswa();
        $req = $this->submitAs($siswa, $this->png('a.png'));
        $path = $req->support_file_path;

        $this->actingAs($this->admin())->post("/permohonan/{$req->id}/setujui");
        $this->actingAs($this->admin())->post("/permohonan/{$req->id}/tanda-tangan");
        $doc = Document::findOrFail($req->fresh()->document_id);
        $this->assertSame($path, $doc->file_path);

        $this->actingAs($this->admin())->delete("/documents/{$doc->id}");
        Storage::disk('public')->assertExists($path);

        // Siswa pemilik dapat mengunduh dokumen resminya sebelum dihapus
    }

    // ---------------- Foto profil ----------------

    public function test_first_avatar_upload_and_display_everywhere(): void
    {
        $u = $this->siswa();
        $this->actingAs($u)->patch('/profile', [
            'name' => $u->name, 'email' => $u->email,
            'avatar' => $this->png('me.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');

        $u->refresh();
        $this->assertNotNull($u->avatar_path);
        Storage::disk('public')->assertExists($u->avatar_path);
        $this->assertStringStartsWith('avatars/', $u->avatar_path);
        $this->assertStringNotContainsString('me.jpg', $u->avatar_path);

        foreach (['/profile', '/dashboard'] as $page) {
            $this->actingAs($u)->get($page)->assertOk()->assertSee($u->avatarUrl(), false);
        }
        $this->actingAs($u)->get($u->avatarUrl())->assertOk();

        // Admin juga dapat melihat foto (konsisten di semua halaman yang login)
        $this->actingAs($this->admin())->get($u->avatarUrl())->assertOk();
    }

    public function test_replacing_avatar_changes_url_and_removes_only_old_file(): void
    {
        $u = $this->siswa();
        $this->actingAs($u)->patch('/profile', ['name' => $u->name, 'email' => $u->email, 'avatar' => $this->png('1.png')]);
        $old = $u->fresh();
        $oldPath = $old->avatar_path; $oldUrl = $old->avatarUrl();

        $this->actingAs($u)->patch('/profile', ['name' => $u->name, 'email' => $u->email, 'avatar' => $this->png('2.png')])
            ->assertSessionHasNoErrors();
        $new = $u->fresh();

        $this->assertNotEquals($oldPath, $new->avatar_path);
        $this->assertNotEquals($oldUrl, $new->avatarUrl());
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($new->avatar_path);
    }

    public function test_saving_profile_without_photo_keeps_existing_avatar_and_data(): void
    {
        $u = $this->siswa();
        $this->actingAs($u)->patch('/profile', ['name' => $u->name, 'email' => $u->email, 'avatar' => $this->png('1.png')]);
        $path = $u->fresh()->avatar_path;

        $this->actingAs($u)->patch('/profile', ['name' => 'Nama Baru', 'email' => $u->email, 'phone' => '0812 3456 7890'])
            ->assertSessionHasNoErrors();

        $u->refresh();
        $this->assertSame($path, $u->avatar_path);
        $this->assertSame('Nama Baru', $u->name);
        Storage::disk('public')->assertExists($path);
    }

    public function test_invalid_and_oversized_avatar_rejected_and_nothing_changes(): void
    {
        $u = $this->siswa();
        $this->actingAs($u)->patch('/profile', ['name' => $u->name, 'email' => $u->email, 'avatar' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('avatar');
        $this->actingAs($u)->patch('/profile', ['name' => $u->name, 'email' => $u->email, 'avatar' => $this->png('big.png')->size(3000)])
            ->assertSessionHasErrors('avatar');
        $this->assertNull($u->fresh()->avatar_path);
    }

    public function test_avatar_persists_across_logout_login_and_default_and_missing_file_fallback(): void
    {
        $u = $this->siswa();
        // Belum punya foto -> avatar default (inisial), halaman tetap OK
        $this->actingAs($u)->get('/dashboard')->assertOk()
            ->assertSee(strtoupper(mb_substr($u->name, 0, 1)), false);

        $this->actingAs($u)->patch('/profile', ['name' => $u->name, 'email' => $u->email, 'avatar' => $this->png('1.png')]);
        $path = $u->fresh()->avatar_path;

        $this->post('/logout');
        $this->assertGuest();
        $this->actingAs($u->fresh())->get('/profile')->assertOk()->assertSee($u->fresh()->avatarUrl(), false);

        // File hilang -> route 404, tapi halaman tetap render dengan fallback inisial
        Storage::disk('public')->delete($path);
        $this->actingAs($u)->get($u->fresh()->avatarUrl())->assertNotFound();
        $this->actingAs($u)->get('/dashboard')->assertOk()->assertSee('onerror', false);
    }

    public function test_remove_avatar(): void
    {
        $u = $this->siswa();
        $this->actingAs($u)->patch('/profile', ['name' => $u->name, 'email' => $u->email, 'avatar' => $this->png('1.png')]);
        $path = $u->fresh()->avatar_path;

        $this->actingAs($u)->patch('/profile', ['name' => $u->name, 'email' => $u->email, 'remove_avatar' => 1])->assertSessionHasNoErrors();
        $this->assertNull($u->fresh()->avatar_path);
        Storage::disk('public')->assertMissing($path);
    }
}