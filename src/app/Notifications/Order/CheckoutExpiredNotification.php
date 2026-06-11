<?php

namespace App\Notifications\Order;

use App\Services\Customer\Order\DTO\FailedOrderDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CheckoutExpiredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public FailedOrderDto $result
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('【Life Station】決済期限切れのお知らせ')
            ->view('mail.orders.customer-expired', [
                'user' => $notifiable,
                'checkoutRequest' => $this->result->checkoutRequest,
                'checkoutItems' => $this->result->checkoutItems
            ]);

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
