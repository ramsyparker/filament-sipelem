<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Schedule extends Model
{
    protected $fillable = [
        'field_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Add relationships with eager loading
    public function field()
    {
        return $this->belongsTo(Field::class)->withDefault();
    }

    // Add scope for optimization
    public function scopeWithBookingData($query)
    {
        return $query->with(['field']);
    }
}