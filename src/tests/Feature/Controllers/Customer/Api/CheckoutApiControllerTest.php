<?php

namespace Tests\Feature\Controllers\Customer\Api;

use App\Models\Cart;
use App\Models\User;
use App\Services\Customer\Order\CheckoutService;
use App\Services\Customer\Order\CheckoutServiceInterface;
use App\Services\Customer\Order\StripeServiceInterface;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutApiControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function store_リクエストが正しければStripeUrlを発行(): void
    {
        $user = User::factory()->create();
        $carts = Cart::factory()->for($user)->count(2)->create();
        $cartIds = $carts->pluck('id')->values()->toArray();

        $mock = Mockery::mock(CheckoutServiceInterface::class);
        $mock->shouldReceive('isValidCartIds')->with($user, $cartIds)->andReturn([]);
        $mock->shouldReceive('reserveStockAndStoreSnap')->andReturn(collect());
        $this->app->instance(CheckoutServiceInterface::class, $mock);

        $response = $this->actingAs($user, 'web')->postJson('/api/checkout', ['ids' => $cartIds]);

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('success', true)
                    ->where('data.checkout_url', 'test-url')
                    ->etc()
            );
    }
    #[Test]
    public function store_Stripeセッションに失敗し500エラーを返す(): void
    {
        $user = User::factory()->create();
        $carts = Cart::factory()->for($user)->count(2)->create();
        $cartIds = $carts->pluck('id')->values()->toArray();

        $mock = Mockery::mock(CheckoutServiceInterface::class);
        $mock->shouldReceive('isValidCartIds')->with($user, $cartIds)->andReturn([]);
        $mock->shouldReceive('reserveStockAndStoreSnap')->andReturn(collect());
        $mock->shouldReceive('rollbackCheckout')->once();
        $this->app->instance(CheckoutServiceInterface::class, $mock);

        $mock2 = Mockery::mock(StripeServiceInterface::class);
        $mock2->shouldReceive('createStripeSession')->andThrow(new Exception()); // ここで例外
        $this->app->instance(StripeServiceInterface::class, $mock2);

        $response = $this->actingAs($user, 'web')->postJson('/api/checkout', ['ids' => $cartIds]);

        $response->assertStatus(500)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('success', false)
                    ->where('message', '決済画面の作成に失敗しました')
                    ->etc()
            );
    }
    
    #[Test]
    public function store_Stripeセッションに失敗し更にロールバックに失敗(): void
    {
        $user = User::factory()->create();
        $carts = Cart::factory()->for($user)->count(2)->create();
        $cartIds = $carts->pluck('id')->values()->toArray();

        $mock = Mockery::mock(CheckoutServiceInterface::class);
        $mock->shouldReceive('isValidCartIds')->with($user, $cartIds)->andReturn([]);
        $mock->shouldReceive('reserveStockAndStoreSnap')->andReturn(collect());
        $mock->shouldReceive('rollbackCheckout')->once()->andThrow(new Exception()); // ロールバック失敗
        $this->app->instance(CheckoutServiceInterface::class, $mock);

        $mock2 = Mockery::mock(StripeServiceInterface::class);
        $mock2->shouldReceive('createStripeSession')->andThrow(new Exception()); // ここで例外
        $this->app->instance(StripeServiceInterface::class, $mock2);

        $response = $this->actingAs($user, 'web')->postJson('/api/checkout', ['ids' => $cartIds]);

        $response->assertStatus(500)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('success', false)
                    ->where('message', '決済画面の作成に失敗しました')
                    ->etc()
            );
    }
    #[Test]
    public function store_認証エラーで401エラーを返す(): void
    {
        $cartIds = [1, 2];
        $response = $this->postJson('/api/checkout', ['ids' => $cartIds]);

        $response->assertStatus(401)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('success', false)
                    ->where('code', 'UNAUTHENTICATED')
                    ->etc()
            );
    }
    #[Test]
    public function store_カートが選択されていないためバリデーションエラー(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'web')->postJson('/api/checkout', ['ids' => []]);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('success', false)
                    ->has('errors')
                    ->where('errors.ids.0', '注文対象がありません、カート画面に戻り選択願います')
                    ->etc()
            );
    }

}
