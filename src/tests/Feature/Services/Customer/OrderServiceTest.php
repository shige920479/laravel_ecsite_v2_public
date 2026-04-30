<?php

namespace Tests\Feature\Services\Customer;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Cart;
use App\Models\CheckoutItem;
use App\Models\CheckoutRequest;
use App\Models\User;
use App\Services\Customer\Order\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Event;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;
    
    #[Test]
    public function createOrder_checkoutRequestを基にオーダー情報を登録できる(): void
    {
        [$user, $checkoutRequest, $checkoutItems] = $this->createCheckoutScenario(3);
        $event = $this->fakeEvent($checkoutRequest->id);

        $orderService = new OrderService();
        $orderService->createOrder($checkoutRequest, $event);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_ex_tax' => $checkoutRequest->total_ex_tax,
            'total_tax' => $checkoutRequest->total_tax,
            'total_in_tax' => $checkoutRequest->total_in_tax,
            'payment_method' => PaymentMethod::CARD,
            'stripe_session_id' => $event->data->object->id,
            'payment_status' => OrderStatus::PAID,
            'ordered_at' => $checkoutRequest->created_at,
        ]);
    }

    private function createCheckoutScenario(int $count = 3)
    {
        $user = User::factory()->create();
        $carts = Cart::factory()->for($user)->count($count)->create();
        
        $checkoutRequest = CheckoutRequest::factory()->for($user)->create();

        $checkoutItems = $carts->map(function ($cart) use ($checkoutRequest) {
            return CheckoutItem::factory()->for($checkoutRequest)->forCart($cart)->create();
        });

        $checkoutRequest->update([
            'total_ex_tax' => $checkoutItems->sum('subtotal_ex_tax'),
            'total_tax' => $checkoutItems->sum('subtotal_tax'),
            'total_in_tax' => $checkoutItems->sum('subtotal_in_tax'),
        ]);

        return [$user, $checkoutRequest, $checkoutItems];
    }

    private function fakeEvent(int $checkoutId): Event
    {
        return Event::constructFrom([
            'id' => 'evt_test_123',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'stripe-session',
                    'metadata' => [
                        'checkout_id' => $checkoutId,
                    ],
                ],
            ],
        ]);
    }
}
