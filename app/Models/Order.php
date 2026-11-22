<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['phone', 'address', 'payment_method'];

    public function laptop() { return $this->belongsTo(Laptop::class); }
    public function buyer() { return $this->belongsTo(User::class, 'buyer_id'); }
    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
    public function review() { return $this->hasOne(Review::class); }
    public function messages() { return $this->hasMany(Message::class); }
}
