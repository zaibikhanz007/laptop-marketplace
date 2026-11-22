<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\QueuedResetPassword;

#[Fillable(['name', 'email', 'password', 'phone', 'city', 'address'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function laptops()
    {
        return $this->hasMany(Laptop::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistLaptops()
    {
        return $this->belongsToMany(Laptop::class, 'wishlists')->withTimestamps();
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'seller_id');
    }

    public function reviewsWritten()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function complaintsFiled()
    {
        return $this->hasMany(Complaint::class, 'complainant_id');
    }

    public function complaintsReceived()
    {
        return $this->hasMany(Complaint::class, 'against_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
    }

    public function averageRating()
    {
        return (float) round($this->reviewsReceived()->avg('rating') ?? 0, 1);
    }

    public function reviewCount(): int
    {
        return (int) $this->reviewsReceived()->count();
    }

    public function buyerOrders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function sellerOrders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

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
            'is_verified' => 'boolean',
        ];
    }
}
