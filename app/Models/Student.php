<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nisn', 'name', 'class', 'entry_year', 'phone', 'email', 'status', 'user_id',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }

    /**
     * Akun login siswa (Portal Akademik), jika sudah dihubungkan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
