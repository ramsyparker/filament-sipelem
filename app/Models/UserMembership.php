<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserMembership extends Model
{
    protected $fillable = [
        'user_id',
        'membership_id',
        'field_id',
        'booking_time',
        'day_of_week',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'booking_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    // Helper method to get next booking date
    public function getNextBookingDate()
    {
        $today = Carbon::now();
        $dayOfWeek = $this->day_of_week;
        
        return $today->copy()->next($dayOfWeek);
    }

    // Check if membership is still valid
    public function isValid()
    {
        return $this->end_date->isFuture() && $this->status === 'active';
    }

    protected static function booted()
    {
        static::created(function ($userMembership) {
            $user = User::find($userMembership->user_id);
            if ($user && $user->role === 'user') {
                $user->update(['role' => 'member']);
            }
        });
    }
}
