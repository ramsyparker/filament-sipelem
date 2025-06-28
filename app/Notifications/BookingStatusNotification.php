<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BookingStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $status;
    protected $orderId;
    protected $fieldName;
    protected $bookingDate;
    protected $startTime;
    protected $endTime;

    public function __construct($status, $orderId, $fieldName, $bookingDate, $startTime, $endTime)
    {
        $this->status = $status;
        $this->orderId = $orderId;
        $this->fieldName = $fieldName;
        $this->bookingDate = $bookingDate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $statusText = [
            'completed' => 'Booking Berhasil',
            'pending' => 'Booking Pending',
            'failed' => 'Booking Gagal',
        ][$this->status] ?? 'Status Booking';

        $mail = (new MailMessage)
            ->subject($statusText . ' - SIPELEM FUTSAL')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Status booking Anda untuk Order ID: ' . $this->orderId . ' adalah: ' . $statusText)
            ->line('Lapangan: ' . $this->fieldName)
            ->line('Tanggal: ' . $this->bookingDate)
            ->line('Waktu: ' . $this->startTime . ' - ' . $this->endTime);

        if ($this->status === 'completed') {
            $mail->line('Booking Anda telah dikonfirmasi. Selamat bermain!');
        } elseif ($this->status === 'pending') {
            $mail->line('Booking Anda masih menunggu pembayaran.');
        } elseif ($this->status === 'failed') {
            $mail->line('Mohon maaf, booking Anda gagal. Silakan coba lagi.');
        }

        $mail->salutation('Salam, Tim SIPELEM FUTSAL');
        return $mail;
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Booking baru/order #' . $this->orderId . ' status: ' . $this->status,
            'order_id' => $this->orderId,
            'status' => $this->status,
            'field' => $this->fieldName ?? null,
            'date' => $this->bookingDate ?? null,
            'start_time' => $this->startTime ?? null,
            'end_time' => $this->endTime ?? null,
        ];
    }
} 