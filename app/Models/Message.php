<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['body'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
