<?php
namespace App\Services\Customer\Order;

use Illuminate\Support\Collection;
use Stripe\StripeClient;

class StripeService implements StripeServiceInterface
{
    public function __construct(private StripeClient $stripeClient)
    {
    }

    public function createStripeSession(Collection $checkoutItems): string
    {
        $lineItems = [];

        foreach ($checkoutItems as $item) {

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item->item_name,
                    ],
                    'unit_amount' => $item->price_in_tax,
                ],
                'quantity' => $item->quantity
            ];
        }

        $checkoutId = $checkoutItems[0]->checkout_request_id;

        $checkoutSession = $this->stripeClient->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel'),
            'metadata' => ['checkout_id' => $checkoutId],
            'expires_at' => now()->addMinutes(30)->timestamp,
        ]);

        return $checkoutSession->url;
    }
}