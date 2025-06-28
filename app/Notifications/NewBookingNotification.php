<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewBookingNotification extends Notification
{
    use Queueable;

    protected $booking;
    protected $user;
    protected $field;

    public function __construct($booking, $user, $field)
    {
        $this->booking = $booking;
        $this->user = $user;
        $this->field = $field;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Booking baru dari ' . $this->user->name,
            'type' => 'new_booking',
            'booking_id' => $this->booking->id,
            'order_id' => $this->booking->order_id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'field_name' => $this->field->name,
            'booking_date' => $this->booking->booking_date,
            'start_time' => $this->booking->start_time,
            'end_time' => $this->booking->end_time,
            'price' => $this->booking->price,
            'status' => $this->booking->status,
        ];
    }
} 