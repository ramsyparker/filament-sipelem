<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $status;
    protected $orderId;
    protected $amount;

    public function __construct($status, $orderId, $amount)
    {
        $this->status = $status;
        $this->orderId = $orderId;
        $this->amount = $amount;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $statusText = [
            'completed' => 'Pembayaran Berhasil',
            'pending' => 'Pembayaran Pending',
            'failed' => 'Pembayaran Gagal',
        ][$this->status] ?? 'Status Pembayaran';

        $mail = (new MailMessage)
            ->subject($statusText . ' - SIPELEM FUTSAL')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Status pembayaran Anda untuk Order ID: ' . $this->orderId . ' adalah: ' . $statusText)
            ->line('Jumlah: Rp ' . number_format($this->amount, 0, ',', '.'));

        if ($this->status === 'completed') {
            $mail->line('Terima kasih, pembayaran Anda telah berhasil.');
        } elseif ($this->status === 'pending') {
            $mail->line('Pembayaran Anda masih dalam proses. Silakan selesaikan pembayaran.');
        } elseif ($this->status === 'failed') {
            $mail->line('Mohon maaf, pembayaran Anda gagal. Silakan coba lagi.');
        }

        $mail->salutation('Salam, Tim SIPELEM FUTSAL');
        return $mail;
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Pembayaran order #' . $this->orderId . ' status: ' . $this->status,
            'order_id' => $this->orderId,
            'status' => $this->status,
            'amount' => $this->amount ?? null,
        ];
    }
} 