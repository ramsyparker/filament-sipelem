<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'order_id', 
        'amount', 
        'status', 
        'payment_method', 
        'payment_token'
    ];

    /**
     * Relasi dengan model User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get booking associated with this payment
     */
    public function booking()
    {
        return $this->hasOne(Booking::class, 'order_id', 'order_id');
    }

    /**
     * Get membership associated with this payment
     */
    public function membership()
    {
        return $this->hasOne(UserMembership::class, 'order_id', 'order_id');
    }

    /**
     * Get order type (booking or membership)
     */
    public function getOrderTypeAttribute()
    {
        if ($this->booking()->exists()) {
            return 'Field Booking';
        } elseif ($this->membership()->exists()) {
            return 'Membership';
        }
        return 'Unknown';
    }
}
