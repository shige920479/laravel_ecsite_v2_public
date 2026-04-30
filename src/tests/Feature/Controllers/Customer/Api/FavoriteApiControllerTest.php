<?php

namespace Tests\Feature\Controllers\Customer\Api;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteApiControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_お気に入り登録一覧のデータをjson形式で返す(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();
        $user->favoriteItems()->syncWithoutDetaching([$item1->id, $item2->id]);
        $other->favoriteItems()->syncWithoutDetaching([$item3->id]);

        $this->assertDatabaseCount('favorites', 3);

        $response = $this->actingAs($user, 'web')->getJson('api/favorite');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'main_image',
                        'shop_name',
                    ]
                ]
            ]);

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$item1->id, $item2->id], $ids);
    }
    
    #[Test]
    public function index_未ログインだと例外を返す(): void
    {
        $response = $this->getJson('api/favorite');

        $response->assertUnauthorized();
    }
    #[Test]
    public function index_お気に入り登録が無い場合は空配列を戻す(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'web')->getJson('api/favorite');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function store_お気に入り登録ができる(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user, 'web')->postJson("/api/items/{$item->id}/favorite");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('favorites', 1)
            ->assertDatabaseHas('favorites', [
                'user_id' => $user->id,
                'item_id' => $item->id
            ]);
    }
    #[Test]
    public function store_販売停止中の場合は例外が返る(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['is_selling' => 0]);

        $response = $this->actingAs($user, 'web')->postJson("/api/items/{$item->id}/favorite");

        $response->assertStatus(400);
        $this->assertDatabaseEmpty('favorites');
    }
    #[Test]
    public function store_未ログインだと例外を返す(): void
    {
        $item = Item::factory()->create();
        
        $response = $this->postJson("/api/items/{$item->id}/favorite");
        
        $response->assertUnauthorized();
    }

    #[Test]
    public function destroy_お気に入り削除ができる(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $user->favoriteItems()->syncWithoutDetaching([$item->id]);

        $response = $this->actingAs($user, 'web')->deleteJson("/api/items/{$item->id}/favorite");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseEmpty('favorites');
    }

    #[Test]
    public function moveToCart_お気に入りからカートに移動する(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $user->favoriteItems()->syncWithoutDetaching([$item->id]);

        $response = $this->actingAs($user, 'web')->postJson("/api/items/{$item->id}/moveToCart");

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('success', true)
                    ->where('message', "商品:{$item->name}をカートへ移動しました")
                    ->etc()
            );
        
        $this->assertDatabaseEmpty('favorites')
            ->assertDatabaseCount('carts', 1);
    }
}
