<?php

namespace App\Notifications\Order;

use App\Services\Customer\Order\DTO\OrderProcessResultDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompletedForCustomerNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public OrderProcessResultDto $result
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
        $shippingFirst = $this->result->shipmentResults->first();
        if (! $shippingFirst) {
            return (new MailMessage)->line('注文情報の取得に失敗しました');
        }

        return (new MailMessage)
            ->subject('【Life Station】お客様のご注文を承りました')
            ->view('mail.orders.customer-completed', [
                'user' => $notifiable,
                'order' => $this->result->order,
                'shippingFirst' => $shippingFirst,
                'shipmentResults' => $this->result->shipmentResults,
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
