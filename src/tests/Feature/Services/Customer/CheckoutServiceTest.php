<?php

namespace Tests\Feature\Services\Customer;

use App\Enums\CheckoutStatus;
use App\Exceptions\OverStockException;
use App\Models\Cart;
use App\Models\CheckoutRequest;
use App\Models\Item;
use App\Models\StockHistory;
use App\Models\User;
use App\Services\Customer\Order\CheckoutService;
use App\Services\Customer\Order\DTO\CheckoutItemDto;
use App\Services\Customer\Order\Exceptions\InValidCartsException;
use Database\Factories\StockFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function isOrderable_チェックアウト可否を判定し正常な場合は空配列を返す(): void
    {
        $user = User::factory()->registered()->create();
        $item1 = Item::factory()->create(['stock_current' => 10]);
        $item2 = Item::factory()->create(['stock_current' => 5]);
        $cart1 = Cart::factory()->for($user)->for($item1)->create(['quantity' => 10]);
        $cart2 = Cart::factory()->for($user)->for($item2)->create(['quantity' => 5]);
        $carts = Cart::whereIn('id', [$cart1->id, $cart2->id])->with('item')->get();

        $res = (new CheckoutService())->isOrderable($user, $carts);

        $this->assertEmpty($res);
    }
    #[Test]
    public function isOrderable_住所未登録でエラーを返す(): void
    {
        $user = User::factory()->create(); // 配送先情報未登録
        $item1 = Item::factory()->create(['stock_current' => 10]);
        $cart1 = Cart::factory()->for($user)->for($item1)->create(['quantity' => 10]);
        $carts = Cart::whereIn('id', [$cart1->id])->with('item')->get();

        $res = (new CheckoutService())->isOrderable($user, $carts);

        $this->assertArrayHasKey('account', $res);
    }
    #[Test]
    public function isOrderable_在庫不足でエラーを返す(): void
    {
        $user = User::factory()->registered()->create();
        $item1 = Item::factory()->create(['stock_current' => 5]);
        $cart1= Cart::factory()->for($user)->for($item1)->create(['quantity' => 10]); // 在庫オーバー
        $carts = Cart::whereIn('id', [$cart1->id, $cart1->id])->with('item')->get();

        $res = (new CheckoutService())->isOrderable($user, $carts);

        $this->assertArrayHasKey("{$cart1->id}", $res);
        $this->assertArrayHasKey("quantity", $res[$cart1->id]);
    }
    #[Test]
    public function isOrderable_販売停止中のためエラーを返す(): void
    {
        $user = User::factory()->registered()->create();
        $item1 = Item::factory()->create(['stock_current' => 10, 'is_selling' => 0]);
        $cart1 = Cart::factory()->for($user)->for($item1)->create(['quantity' => 1]);
        $carts = Cart::whereIn('id', [$cart1->id])->with('item')->get();

        $res = (new CheckoutService())->isOrderable($user, $carts);

        $this->assertArrayHasKey("{$cart1->id}", $res);
        $this->assertArrayHasKey("quantity", $res[$cart1->id]);
    }

    #[Test]
    public function isValidCartIds_カートがユーザーの登録したものであれば何も返さない(): void
    {
        $user = User::factory()->registered()->create();
        $item1 = Item::factory()->create(['stock_current' => 10]);
        $item2 = Item::factory()->create(['stock_current' => 5]);
        $cart1 = Cart::factory()->for($user)->for($item1)->create(['quantity' => 1]);
        $cart2 = Cart::factory()->for($user)->for($item2)->create(['quantity' => 1]);

        $res = (new CheckoutService())->isValidCartIds($user, [$cart2->id]);

        $this->assertEmpty($res);
    }
    #[Test]
    public function isValidCartIds_他人のカートがあれば例外を投げる(): void
    {
        $user = User::factory()->registered()->create();
        $other = User::factory()->registered()->create();
        $item1 = Item::factory()->create(['stock_current' => 10]);
        $item2 = Item::factory()->create(['stock_current' => 5]);
        $cart1 = Cart::factory()->for($user)->for($item1)->create(['quantity' => 1]);
        $cart2 = Cart::factory()->for($other)->for($item2)->create(['quantity' => 1]);

        $this->expectException(InValidCartsException::class);
        (new CheckoutService())->isValidCartIds($user, [$cart1->id, $cart2->id]);
    }

    #[Test]
    public function reserveStockAndStoreSnap_在庫確保とスナップ登録しスナップデータを返す(): void
    {
        $user = User::factory()->create();
        $item1 = Item::factory()->create(['stock_current' => 100]);
        $item2 = Item::factory()->create(['stock_current' => 50]);
        $cart1 = Cart::factory()->for($user)->for($item1)->create(['quantity' => 10]);
        $cart2 = Cart::factory()->for($user)->for($item2)->create(['quantity' => 5]);
        $carts = Cart::query()
            ->whereIn('id', [$cart1->id, $cart2->id])
            ->where('user_id', $user->id)
            ->with('item')
            ->get();
        
        $cartItems = $carts->map(fn ($cart) => CheckoutItemDto::getItemData($user, $cart));

        $res = (new CheckoutService())->reserveStockAndStoreSnap($cartItems);
        
        $this->assertDatabaseHas('items', [
            'id' => $item1->id,
            'stock_current' => (100 - 10),
        ]);
        $this->assertDatabaseHas('items', [
            'id' => $item2->id,
            'stock_current' => (50 - 5),
        ]);
        $this->assertDatabaseCount('stock_histories', 2);
        $this->assertDatabaseHas('checkout_requests', [
            'user_id' => $user->id,
            'status' => CheckoutStatus::PENDING,
            'total_ex_tax' => $cartItems[0]->subtotalExTax + $cartItems[1]->subtotalExTax,
            'total_tax' => $cartItems[0]->subtotalTax + $cartItems[1]->subtotalTax,
            'total_in_tax' => $cartItems[0]->subtotalInTax + $cartItems[1]->subtotalInTax,
        ]);
        $this->assertDatabaseCount('checkout_items', 2);
        $this->assertCount(2, $res);
    }

    #[Test]
    public function reserveStockAndStoreSnap_注文数が在庫を超えていたら例外を投げロールバックする(): void
    {
        $user = User::factory()->create();
        $item1 = Item::factory()->create(['stock_current' => 7]);
        $item2 = Item::factory()->create(['stock_current' => 4]);
        $cart1 = Cart::factory()->for($user)->for($item1)->create(['quantity' => 6]); // 1回名正常
        $cart2 = Cart::factory()->for($user)->for($item2)->create(['quantity' => 5]); // 2回目在庫不足
        $carts = Cart::query()
            ->whereIn('id', [$cart1->id, $cart2->id])
            ->where('user_id', $user->id)
            ->with('item')
            ->get();

        $cartItems = $carts->map(fn ($cart) => CheckoutItemDto::getItemData($user, $cart));

        $this->expectException(OverStockException::class);

        try {
            (new CheckoutService())->reserveStockAndStoreSnap($cartItems);
        
        } catch (OverStockException $e) {
            $this->assertDatabaseEmpty('checkout_requests');
            $this->assertDatabaseEmpty('stock_histories');
            $this->assertDatabaseHas('items', [
                'id' => $item1->id,
                'stock_current' => 7,
            ]);
            $this->assertDatabaseHas('items', [
                'id' => $item2->id,
                'stock_current' => 4,
            ]);
            $this->assertDatabaseEmpty('checkout_items');

            throw $e;
        }
    }

    #[Test]
    public function checkoutRollBack_StripeUrlの生成に失敗し在庫を戻しスナップのステータス変更(): void
    {
        $user = User::factory()->create();
        $item1 = Item::factory()->create(['stock_current' => 100]);
        $item2 = Item::factory()->create(['stock_current' => 50]);
        $cart1 = Cart::factory()->for($user)->for($item1)->create(['quantity' => 10]);
        $cart2 = Cart::factory()->for($user)->for($item2)->create(['quantity' => 5]);
        $carts = Cart::query()
            ->whereIn('id', [$cart1->id, $cart2->id])
            ->where('user_id', $user->id)
            ->with('item')
            ->get();
        
        $cartItems = $carts->map(fn ($cart) => CheckoutItemDto::getItemData($user, $cart));

        $checkoutService = new CheckoutService();
        $checkoutItems = $checkoutService->reserveStockAndStoreSnap($cartItems);

        $this->assertDatabaseHas('checkout_requests', [
            'status' => CheckoutStatus::PENDING
        ]);

        $checkoutService->rollbackCheckout($checkoutItems, CheckoutStatus::FAILED, 'test-reason');
        $reasons = StockHistory::latest('id')->take(2)->pluck('reason')->values();

        $this->assertDatabaseHas('items', [
            'id' => $item1->id,
            'stock_current' => 100,
        ]);
        $this->assertDatabaseHas('items', [
            'id' => $item2->id,
            'stock_current' => (50),
        ]);
        $this->assertDatabaseCount('stock_histories', 4);
        $this->assertSame('test-reason', $reasons[0]);
        $this->assertSame('test-reason', $reasons[1]);
        $this->assertDatabaseHas('checkout_requests', [
            'id' => $checkoutItems[0]->checkout_request_id,
            'status' => CheckoutStatus::FAILED,
        ]);
        $this->assertDatabaseCount('checkout_items', 2);
    }

    #[Test]
    public function updateStatus_決済完了後にチェックアウトステータスを更新する(): void
    {
        $checkoutRequest = CheckoutRequest::factory()->create();

       ( new CheckoutService())->updateStatus($checkoutRequest, CheckoutStatus::COMPLETED);

       $this->assertDatabaseCount('checkout_requests', 1)
            ->assertDatabaseHas('checkout_requests', [
                'id' => $checkoutRequest->id,
                'status' => CheckoutStatus::COMPLETED,
            ]);
    }
}
