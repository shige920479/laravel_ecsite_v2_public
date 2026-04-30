<?php

namespace Tests\Feature\Services\Customer;

use App\Models\Cart;
use App\Models\CheckoutItem;
use App\Models\CheckoutRequest;
use App\Models\User;
use App\Services\Customer\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deleteFromCheckout_決済完了後にカートを削除する(): void
    {
        [$user, $checkoutRequest, $checkoutItems] = $this->createCheckoutScenario(3);

        $this->assertDatabaseCount('carts', 3);

        (new CartService())->deleteFromCheckout($checkoutRequest);

        $this->assertDatabaseEmpty('carts');
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
}
