<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = ['subject', 'description'];

    public function complainant() { return $this->belongsTo(User::class, 'complainant_id'); }
    public function against() { return $this->belongsTo(User::class, 'against_user_id'); }
}
