<?php

namespace Tests\Feature\Controllers\Customer;

use App\Models\Cart;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Customer\Order\CheckoutServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutPageControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function confirm_注文確認画面を表示する(): void
    {
        $user = User::factory()->create();
        $carts = Cart::factory()->for($user)->count(2)->create();
        $cartIds = $carts->pluck('id')->values()->toArray();

        $mock = Mockery::mock(CheckoutServiceInterface::class);
        $mock->shouldReceive('isOrderable')->andReturn([]);
        $this->app->instance(CheckoutServiceInterface::class, $mock);

        $response = $this->actingAs($user, 'web')->post(route('checkout.confirm'), ['ids' => $cartIds]);

        $response->assertOk()
            ->assertViewIs('user.checkout.confirm')
            ->assertViewHas('carts', fn ($carts) =>
                $carts->count() === 2
            );
    }

    #[Test]
    public function confirm_注文できないアイテムがありカート画面へ戻る(): void
    {
        $user = User::factory()->create();
        $carts = Cart::factory()->for($user)->count(2)->create();
        $cartIds = $carts->pluck('id')->values()->toArray();

        $mock = Mockery::mock(CheckoutServiceInterface::class);
        $mock->shouldReceive('isOrderable')->andReturn(['error' => 'test-error']);
        $this->app->instance(CheckoutServiceInterface::class, $mock);

        $response = $this->actingAs($user, 'web')->post(route('checkout.confirm'), ['ids' => $cartIds]);

        $response->assertRedirectBackWithErrors(['error' => 'test-error']);
    }
    #[Test]
    public function success_決済成功時には購入内容を表示する(): void
    {
        $user = User::factory()->registered()->create();
        $sessionId = $this->createOrderScenario($user);

        $response = $this->actingAs($user, 'web')->get(route('checkout.success', ['session_id' => $sessionId]));

        $response->assertOk()
            ->assertViewIs('user.checkout.success')
            ->assertViewHas('order')
            ;
    }
    #[Test]
    public function cancel_キャンセル画面を表示する(): void
    {
        $user = User::factory()->registered()->create();

        $response = $this->actingAs($user, 'web')->get(route('checkout.cancel'));

        $response->assertOk()
            ->assertViewIs('user.checkout.cancel');
    }

    private function createOrderScenario(User $user): string
    {
        $item = Item::factory()->withMainImage()->create();
        
        // 金額の整合性は考慮外
        $order = Order::factory()->for($user)->create(['stripe_session_id' => 'cs_test_123']);
        $shipment = Shipment::factory()->for($order)->for($item->shop)->forUser($user)->create();
        $orderItem = OrderItem::factory()->for($shipment)->forItem($item, 1)->create();

        return $order->stripe_session_id;
    }
}
