<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'field_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'price',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'field_id', 'field_id')
            ->where('start_time', Carbon::parse($this->booking_date)->setTimeFromTimeString($this->start_time))
            ->where('end_time', Carbon::parse($this->booking_date)->setTimeFromTimeString($this->end_time));
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($booking) {
            // Update schedule when booking is created
            self::updateScheduleStatus($booking, 'booked');
        });

        static::updated(function ($booking) {
            // Update schedule when booking status changes
            if ($booking->isDirty('status')) {
                $newStatus = $booking->status === 'cancelled' ? 'available' : 'booked';
                self::updateScheduleStatus($booking, $newStatus);
            }
        });

        static::deleted(function ($booking) {
            // Reset schedule when booking is deleted
            self::updateScheduleStatus($booking, 'available');
        });
    }

    private static function updateScheduleStatus($booking, $status)
    {
        $bookingDate = Carbon::parse($booking->booking_date);
        $startTime = Carbon::parse($booking->start_time);
        $endTime = Carbon::parse($booking->end_time);

        Schedule::where('field_id', $booking->field_id)
            ->whereDate('start_time', $bookingDate->format('Y-m-d'))
            ->whereTime('start_time', $startTime->format('H:i:s'))
            ->whereTime('end_time', $endTime->format('H:i:s'))
            ->update(['status' => $status]);
    }
}
