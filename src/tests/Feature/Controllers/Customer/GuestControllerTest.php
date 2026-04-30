<?php

namespace Tests\Feature\Controllers\Customer;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Review;
use App\Models\SubCategory;
use App\Models\User;
use App\Services\Customer\Item\RankingQueryServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_パラメータとカテゴリーツリーをブレードに展開(): void
    {
        $category = Category::factory()->create();
        $subCate = SubCategory::factory()->for($category)->create();
        $itemCate = ItemCategory::factory()->for($subCate)->create();

        $response = $this->get(
            route('itemCategory.index', [
                'category' => $category,
                'subCategory' => $subCate,
                'itemCategory' => $itemCate,
                'item_search' => 'search', 'item_sort' => 'price_asc', 'per_page' => 12, 'page' => 2,
            ])
        );

        $response->assertOk()
            ->assertViewIs('user.items.index')
            ->assertViewHas('viewData.category', $category->slug)
            ->assertViewHas('viewData.sub_category', $subCate->slug)
            ->assertViewHas('viewData.item_category', $itemCate->slug)
            ->assertViewHas('viewData.item_search', 'search')
            ->assertViewHas('viewData.item_sort', 'price_asc')
            ->assertViewHas('viewData.per_page', "12")
            ->assertViewHas('viewData.page', "2")
            ->assertViewHas('categories', fn ($categories) =>
                $categories->first()['slug'] === $category->slug
            )
            ;
    }
    #[Test]
    public function index_パラメーター無でルートにアクセス(): void
    {
        $response = $this->get(route('home.index'));

        $response->assertOk()
            ->assertViewHas('viewData')
            ->assertViewHas('categories', fn($categories) =>
                $categories->count() === 0
            );
    }
    #[Test]
    public function index_未定義のソートキーが入力されたら302とメッセージを返す(): void
    {
        $response = $this->get(
            route('home.index', ['item_sort' => 'invalid'])
        );

        $response->assertStatus(302)
            ->assertSessionHasErrors('item_sort');
    }
    #[Test]
    public function show_商品情報を取得し整形したうえでViewに渡す(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $item = Item::factory()->withImages()->create();
        $otherItems = Item::factory()->withImages()->count(2)->create(); // 他のアイテムも追加しておく
        $review1 = Review::factory()->for($item)->for($user1)->create(['star' => 3]);
        $review2 = Review::factory()->for($item)->for($user2)->create(['star' => 5]);

        $response = $this->get(route('item.show', ['item' => $item]));

        $response->assertOk()
            ->assertViewIs('user.items.show')
            ->assertViewHas('isFavorite', false)
            ->assertViewHas('prevUrl', '/')
            ->assertViewHas('item.id', fn ($id) => $id === $item->id)
            ->assertViewHas('item.shop_name', $item->shop->name)
            ->assertViewHas('item.item_category', $item->itemCategory->name)
            ->assertViewHas('item.avg_star', 4.0)
            ->assertViewHas('item.reviews_count', 2)
            ->assertViewHas('item.images', 
                fn ($images) => count($images) === count($item->itemImages))
            ;
    }

    #[Test]
    public function show_認証済であればお気に入りを表示できる(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->withImages()->create();
        $user->favoriteItems()->attach($item);

        $response = $this->actingAs($user, 'web')->get(route('item.show', ['item' => $item]));
        $response->assertOk()
            ->assertViewHas('isFavorite', true);
    }

    #[Test]
    public function show_未認証ではお気に入りを表示できない(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->withImages()->create();
        $user->favoriteItems()->attach($item);
        
        $response = $this->get(route('item.show', ['item' => $item]));
        $response->assertOk()
            ->assertViewHas('isFavorite', false);
    }

    #[Test]
    public function show_パラメータがあればprevUrlにセットされる(): void
    {
        $item = Item::factory()->withImages()->create();

        $response = $this->get(route('item.show', [
            'item' => $item,
            'from' => '/mug/design'
        ]));

        $response->assertViewHas('prevUrl', '/mug/design');
    }

    #[Test]
    public function ranking_指定したビューを返す(): void
    {
        $items = Item::factory()->withMainImage()->count(2)->create();
        $mock = Mockery::mock(RankingQueryServiceInterface::class);
        $mock->shouldReceive('getRankedItems')->once()->andReturn($items);
        $this->app->instance(RankingQueryServiceInterface::class, $mock);

        $response = $this->get(route('items.ranking'));

        $response->assertOk()
            ->assertViewIs('user.items.ranking')
            ->assertViewHas('items', fn ($items) => $items->count() === 2)
            ->assertViewHas('categories');
    }
}
