<?php

namespace App\Notifications\Order;

use App\Models\Order;
use App\Services\Customer\Order\DTO\ShipmentResultDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class OrderReceivedForOwnerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
        public ShipmentResultDto $result
    )
    {
        //
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
        $totalInTax = $this->result->orderItems->sum('subtotal_in_tax');
        return (new MailMessage)
            ->subject('【Life Station】新規注文がありました')
            ->view('mail.orders.owner-received', [
                'order' => $this->order,
                'shipment' => $this->result->shipment,
                'orderItems' => $this->result->orderItems,
                'totalInTax' => $totalInTax, 
                'owner' => $notifiable,
            ])
            ;
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
