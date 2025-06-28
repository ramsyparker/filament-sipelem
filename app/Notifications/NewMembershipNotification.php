<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewMembershipNotification extends Notification
{
    use Queueable;

    protected $userMembership;
    protected $user;
    protected $membership;
    protected $field;

    public function __construct($userMembership, $user, $membership, $field)
    {
        $this->userMembership = $userMembership;
        $this->user = $user;
        $this->membership = $membership;
        $this->field = $field;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Membership baru dari ' . $this->user->name,
            'type' => 'new_membership',
            'user_membership_id' => $this->userMembership->id,
            'order_id' => $this->userMembership->order_id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'membership_name' => $this->membership->name,
            'field_name' => $this->field->name,
            'day_of_week' => $this->userMembership->day_of_week,
            'booking_time' => $this->userMembership->booking_time,
            'start_date' => $this->userMembership->start_date,
            'end_date' => $this->userMembership->end_date,
            'price' => $this->membership->price,
            'status' => $this->userMembership->status,
        ];
    }
}