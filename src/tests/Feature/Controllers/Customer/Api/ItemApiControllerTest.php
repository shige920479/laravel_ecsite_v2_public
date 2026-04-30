<?php

namespace Tests\Feature\Controllers\Customer\Api;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemApiControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_指定したjson形式でデータを返す(): void
    {
        $response = $this->getJson('/api/items');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'shop_name',
                        'name',
                        'price',
                        'main_image',
                        'avg_star',
                        'reviews_count',
                        'is_selling'
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'total'
                ]
            ])
            ;
    }

    #[Test]
    public function index_検索結果が0件でも正常なjsonを返す(): void
    {
        $response = $this->getJson('/api/items?item_search=test');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure([
                'meta' => [
                    'current_page',
                    'last_page',
                    'total'
                ]
            ])
            ;
    }

    #[Test]
    public function index_存在しない並べ替えキーが入力されたらエラーを返す(): void
    {
        $response = $this->getJson('/api/items?item_sort=invalid-sort');

        $response->assertStatus(422);
    }
    #[Test]
    public function index_存在しないカテゴリーのパスが送信されたらエラーを返す(): void
    {
        $response = $this->getJson('/api/items/invalid');

        $response->assertStatus(404);
    }
    
    #[Test]
    public function index_ページ指定で取得件数が変わる(): void
    {
        Item::factory()->count(10)->create();

        $response = $this->getJson('api/items?per_page=8');
        
        $response->assertOk()
            ->assertJsonCount(8, 'data');

        $response = $this->getJson('api/items?per_page=8&page=2');
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function index_許可されていないper_pageはエラー(): void
    {
        $response = $this->getJson('/api/items?per_page=7');

        $response->assertStatus(422);
    }
}


