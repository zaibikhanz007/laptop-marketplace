<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaptopImage extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'is_cover'];

    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
    }

    public function laptop() { return $this->belongsTo(Laptop::class); }
}
