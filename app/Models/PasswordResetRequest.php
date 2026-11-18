<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    protected $fillable = ['user_id', 'student_id', 'note', 'status', 'handled_by', 'handled_at'];

    protected $casts = ['handled_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function student() { return $this->belongsTo(Student::class); }
    public function handler() { return $this->belongsTo(User::class, 'handled_by'); }
}
