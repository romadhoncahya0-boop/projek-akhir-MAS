<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'preferences' => 'array',
            'last_login_at' => 'datetime',
            'previous_login_at' => 'datetime',
        ];
    }

    /**
     * Data siswa terhubung, hanya terisi jika role = siswa.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isPetugasTu(): bool
    {
        return in_array($this->role, ['admin', 'petugas']);
    }

    /** Preferensi notifikasi (bawaan: aktif). */
    public function wantsNotification(string $key): bool
    {
        return (bool) ($this->preferences['notif'][$key] ?? true);
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        // Lewat route terautentikasi (tanpa symlink). ?v= berubah setiap kali foto diganti
        // sehingga browser tidak menampilkan foto lama dari cache.
        return route('files.avatar', ['user' => $this->id, 'v' => substr(md5($this->avatar_path), 0, 10)], false);
    }
}
