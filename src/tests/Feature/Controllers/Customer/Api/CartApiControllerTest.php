<?php

namespace Tests\Feature\Controllers\Customer\Api;

use App\Models\Cart;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartApiControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_カート一覧のデータをjson形式で返す(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();
        $cart1 = Cart::factory()->for($user)->for($item1)->create();
        $cart2 = Cart::factory()->for($user)->for($item2)->create();
        $cart3 = Cart::factory()->for($other)->for($item3)->create();

        $response = $this->actingAs($user, 'web')->getJson('/api/cart');
        $cartIds = collect($response->json('data'))->pluck('id')->toArray();

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'item_id',
                        'item_name',
                        'item_price',
                        'quantity',
                        'main_image',
                        'shop_name'
                    ]
                ],
                'message',
            ]);
            
            ;
        $this->assertEqualsCanonicalizing([$cart1->id, $cart2->id], $cartIds);
    }
    #[Test]
    public function index_カート登録が無い場合は空配列を返す(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->getJson('/api/cart');

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('success', true)
                    ->where('data', [])
                    ->etc()
            );
    }

    #[Test]
    public function store_カートに登録しメッセージを返す(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['stock_current' => 20]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/cart', ['item_id' => $item->id, 'quantity' => 3]);
        
        $response->assertOk()
            ->assertjson(fn (AssertableJson $json) =>
                $json->where('success', true)
                    ->where('message', 'この商品をカートに登録しました')
                    ->etc()
            );
        
        $this->assertDatabaseCount('carts', 1)
            ->assertDatabaseHas('carts', [
                'user_id' => $user->id,
                'item_id' => $item->id,
                'quantity' => 3,
            ]);
    }

    #[Test]
    public function store_同じ商品はカート登録できないので例外を返す(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['stock_current' => 20]);
        $user->carts()->create([
            'item_id' => $item->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/cart', ['item_id' => $item->id, 'quantity' => 3]);

        $response->assertStatus(409);
    }
    #[Test]
    public function store_販売停止中はカート登録できないので例外を返す(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['is_selling' => 0]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/cart', ['item_id' => $item->id, 'quantity' => 3]);

        $response->assertStatus(400);
    }
    #[Test]
    public function store_在庫数を超えているとカート登録できないので例外を返す(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['stock_current' => 1]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/cart', ['item_id' => $item->id, 'quantity' => 3]);

        $response->assertStatus(409);
    }
    #[Test]
    public function store_未ログインは401(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson('/api/cart', [
            'item_id' => $item->id,
            'quantity' => 1
        ]);

        $response->assertUnauthorized();
    }
    
    #[Test]
    public function update_カートの数量を更新する():void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['stock_current' => 20]);
        $cart = Cart::factory()->for($user)->for($item)->create(['quantity' => 1]);

        $response = $this->actingAs($user, 'web')
            ->patchJson("/api/cart/{$cart->id}", ['quantity' => 3]);

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('success', true)
                    ->where('message', "{$cart->item->name}の数量を変更しました")
                    ->etc()
            );
        
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'quantity' => 3
        ]);
    }

    #[Test]
    public function update_他人のカートは更新できない(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $item = Item::factory()->create();
        $cart = Cart::factory()->for($other)->for($item)->create(['quantity' => 1]);

        $response = $this->actingAs($user, 'web')
            ->patchJson("/api/cart/{$cart->id}", ['quantity' => 3]);

        $response->assertForbidden();
    }

    #[Test]
    public function destroy_カート登録を削除する(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $cart = Cart::factory()->for($user)->for($item)->create();

        $response = $this->actingAs($user, 'web')->deleteJson("/api/cart/{$cart->id}");

        $response->assertOk()
            ->assertJson(['message' => "{$item->name}を削除しました"]);
        $this->assertDatabaseMissing('carts', [
            'id' => $cart->id
        ]);
    }
}
