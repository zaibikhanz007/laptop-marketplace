<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LaptopImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'cloudinary_public_id',
        'is_cover',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    /**
     * Get image URL.
     * Supports both Cloudinary URLs and old local images.
     */
    public function getUrlAttribute(): string
    {
        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        return asset('storage/' . $this->path);
    }

    public function laptop()
    {
        return $this->belongsTo(Laptop::class);
    }
}
