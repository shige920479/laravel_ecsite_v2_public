<?php
namespace App\Services\Customer\Order;

use App\Models\CheckoutRequest;
use App\Models\Order;
use Stripe\Event;

interface WebhookServiceInterface
{
    /** 署名検証 */
    public function verifyWebhook(string $payload, string $sigHeader): Event;

    public function handle(Event $event): void;

}