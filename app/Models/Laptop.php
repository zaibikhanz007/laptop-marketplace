<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laptop extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand', 'model', 'processor', 'ram', 'storage',
        'condition', 'price', 'location', 'description',
        'payment_account_title', 'payment_account_number', 'payment_bank_name',
    ];

    public function seller() { return $this->belongsTo(User::class, 'user_id'); }
    public function images() { return $this->hasMany(LaptopImage::class)->orderByDesc('is_cover')->orderBy('id'); }
    public function orders() { return $this->hasMany(Order::class); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }

    public function scopeAvailable(Builder $q) { return $q->where('status', 'available')->where('approval_status', 'approved'); }
    public function scopeFeatured(Builder $q) { return $q->where('is_featured', true)->where(fn ($query) => $query->whereNull('featured_until')->orWhereDate('featured_until', '>=', today())); }

    public function scopeFilter(Builder $q, array $f)
    {
        return $q
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where(fn ($w) =>
                $w->where('brand', 'like', "%$v%")->orWhere('model', 'like', "%$v%")))
            ->when($f['brand'] ?? null, fn ($q, $v) => $q->where('brand', $v))
            ->when($f['condition'] ?? null, fn ($q, $v) => $q->where('condition', $v))
            ->when($f['city'] ?? null, fn ($q, $v) => $q->where('location', $v))
            ->when(isset($f['min_price']), fn ($q) => $q->where('price', '>=', $f['min_price']))
            ->when(isset($f['max_price']), fn ($q) => $q->where('price', '<=', $f['max_price']));
    }

    public function scopeSortBy(Builder $q, ?string $sort)
    {
        return match ($sort) {
            'newest' => $q->latest(),
            'price_low' => $q->orderBy('price'),
            'price_high' => $q->orderByDesc('price'),
            default => $q->orderByRaw('(is_featured = 1 AND (featured_until IS NULL OR featured_until >= ?)) DESC', [today()->toDateString()])->latest(),
        };
    }

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'featured_until' => 'date',
        ];
    }
}
