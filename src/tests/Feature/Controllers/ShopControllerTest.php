<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\Owner\ShopController;
use App\Models\Owner;
use App\Models\Shop;
use App\Models\User;
use App\Services\Owner\ShopService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_オーナーのショップ情報を表示する(): void
    {
        $owner = Owner::factory()->create();

        $res = $this->actingAs($owner, 'web_owner')->get(route('owner.shop.index'));
        
        $res->assertOk();
        $res->assertViewIs('owner.home');
        $res->assertViewHas('owner');
        $res->assertSee('ショップ情報がありません、新規登録願います'); // ショップ未登録のケース
    }
        
    #[Test]
    public function create_ショップ登録画面を表示する():void
    {
        $owner = Owner::factory()->create();
        $res = $this->actingAs($owner, 'web_owner')->get(route('owner.shop.create'));

        $res->assertOk();
        $res->assertViewIs('owner.shop.create');
    }
    #[Test]
    public function create_オーナー以外はショップ登録画面を表示できない():void
    {
        $user = User::factory()->create(); // ユーザー
        $res = $this->actingAs($user)->get(route('owner.shop.create'));

        $res->assertStatus(302);
    }

    #[Test]
    public function store_ショップ情報を登録しホームにリダイレクト(): void
    {
        $owner = Owner::factory()->create();
        $input = $this->getRequest();
        session(['tmp_image' => 'dummy']);

        $mock = Mockery::mock(ShopService::class);
        $mock->shouldReceive('create')->once();

        $this->app->instance(ShopService::class, $mock);
        $res = $this->actingAs($owner, 'web_owner')->post(route('owner.shop.store'), $input);

        $res->assertRedirect(route('owner.shop.index'))
            ->assertSessionHasAll([
                'status' => 'info',
                'message' => '新規ショップを登録しました'
            ])
            ->assertSessionMissing('tmp_image');
    }

    #[Test]
    public function store_システムエラーでリダイレクト(): void
    {
        $owner = Owner::factory()->create();
        $input = $this->getRequest();
        session(['tmp_image' => 'dummy']);

        $mock = Mockery::mock(ShopService::class);
        $mock->shouldReceive('create')->once()->andThrow(new \Exception('DB Error'));
        
        $this->app->instance(ShopService::class, $mock);
        $res = $this->actingAs($owner, 'web_owner')
            ->from(route('owner.shop.create'))
            ->post(route('owner.shop.store'), $input);
        
        $res->assertRedirect(route('owner.shop.create'))
            ->assertSessionHasAll([
                'status' => 'alert',
                'message' => 'システムエラーが発生しました。お手数ですが時間をおいて再度お試しください。'
            ]) 
            ->assertSessionMissing('tmp_image');
    }

    #[Test]
    public function edit_ショップ編集画面を表示する():void
    {
        $owner = Owner::factory()->create();
        $shop = Shop::factory()->for($owner)->create();

        $res = $this->actingAs($owner, 'web_owner')->get(route('owner.shop.edit', ['shop' => $shop]));

        $res->assertOk();
        $res->assertViewIs('owner.shop.edit');
        $res->assertViewHas('shop');
    }

    #[Test]
    public function edit_オーナー以外はショップ編集画面を表示できない():void
    {
        $user = User::factory()->create(); // ユーザー
        $shop = Shop::factory()->create();

        $res = $this->actingAs($user)->get(route('owner.shop.edit', ['shop' => $shop]));

        $res->assertStatus(302);
    }

    #[Test]
    public function update_オーナーはショップ情報を更新できる(): void
    {
        $owner = Owner::factory()->create();
        $shop = Shop::factory()->for($owner)->create();
        $tmpImagePath = 'tmp/1/dummy.webp';
        session(['tmp_image' => $tmpImagePath]);
        $input = $this->getRequest();

        $mock = Mockery::mock(ShopService::class);
        $mock->shouldReceive('update')->once();
        $this->app->instance(ShopService::class, $mock);

        $res = $this->actingAs($owner, 'web_owner')
            ->put(route('owner.shop.update', ['shop' => $shop]), $input);

        $res->assertRedirect(route('owner.shop.index'))
            ->assertSessionHasAll([
                'status' => 'info',
                'message' => 'ショップ情報を更新しました'
            ])
            ->assertSessionMissing('tmp_image');
    }
    #[Test]
    public function update_変更がなければ編集画面に戻る(): void
    {
        $owner = Owner::factory()->create();
        $shop = Shop::factory()->for($owner)->create();
        $input = [
            'name' => $shop->name,
            'information' => $shop->information,
            'is_selling' => $shop->is_selling
        ];

        $res = $this->actingAs($owner, 'web_owner')
            ->from(route('owner.shop.edit', ['shop' => $shop]))
            ->put(route('owner.shop.update', ['shop' => $shop]), $input);

        $res->assertRedirect(route('owner.shop.edit', ['shop' => $shop]))
            ->assertSessionHasAll([
                'status' => 'alert',
                'message' => '登録された内容に変更がありません、お手数ですが再度入力願います'
            ]);
    }
    
    #[Test]
    public function update_システムエラーで編集画面に戻る(): void
    {
        $owner = Owner::factory()->create();
        $shop = Shop::factory()->for($owner)->create();
        $tmpImagePath = 'tmp/1/dummy.webp';
        session(['tmp_image' => $tmpImagePath]);
        $input = $this->getRequest();

        $mock = Mockery::mock(ShopService::class);
        $mock->shouldReceive('update')->once()->andThrow(new \Exception('DB Error'));
        $this->app->instance(ShopService::class, $mock);

        $res = $this->actingAs($owner, 'web_owner')
            ->from(route('owner.shop.edit', ['shop' => $shop]))
            ->put(route('owner.shop.update', ['shop' => $shop]), $input);

        $res->assertRedirect(route('owner.shop.edit', ['shop' => $shop]))
            ->assertSessionHasAll([
                'status' => 'alert',
                'message' => 'システムエラーが発生しました。お手数ですが時間をおいて再度お試しください。'
            ])
            ->assertSessionMissing('tmp_image');
    }

    private function getRequest(): array
    {
        return [
            'name' => 'test-shop',
            'information' => 'test-info',
            'is_selling' => 1,
        ];
    }

}
