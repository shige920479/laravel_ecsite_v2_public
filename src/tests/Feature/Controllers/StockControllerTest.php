<?php

namespace Tests\Feature\Controllers;

use App\Models\Item;
use App\Models\Owner;
use App\Models\StockHistory;
use App\Services\Owner\StockCsvImportService;
use App\Services\Owner\StockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockControllerTest extends TestCase
{
    use RefreshDatabase;
    private Owner $owner;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));
        $this->owner = Owner::factory()->withShop()->create();
        $this->item = Item::factory()
            ->for($this->owner->shop)->withMainImage()->create(['stock_current' => 0]);
    }

    #[Test]
    public function index_在庫履歴一覧を表示できる(): void
    {
        $histories = StockHistory::factory()->for($this->item)->count(3)->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.stocks.index', ['item' => $this->item]));

        $res->assertOk()
            ->assertViewIs('owner.stocks.index')
            ->assertViewHas(['histories', 'item']);
    }
    #[Test]
    public function index_日付指定で在庫履歴一覧を表示できる(): void
    {
        $before10 = $this->createHistory(10);
        $before20 = $this->createHistory(20);
        $before30 = $this->createHistory(30);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.stocks.index', [
                'item' => $this->item,
                'start_date' => now()->subDays(25)->format('Y-m-d'),
                'end_date' => now()->subDays(1)->format('Y-m-d')
            ]));

        $res->assertOk()
            ->assertViewHas('histories', fn ($histories) => 
                $histories->count() === 2
            )
            ;
    }
    #[Test]
    public function index_タイプ指定で在庫履歴一覧を表示できる(): void
    {
        StockHistory::factory()->for($this->item)->count(3)->create();
        StockHistory::factory()->for($this->item)->order(1)->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.stocks.index', [
                'item' => $this->item,
                'type' => 'in'
            ]));

        $res->assertOk()
            ->assertViewHas('histories', fn ($histories) => 
                $histories->count() === 3
            );
        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.stocks.index', [
                'item' => $this->item,
                'type' => 'out'
            ]));

        $res->assertOk()
            ->assertViewHas('histories', fn ($histories) => 
                $histories->count() === 1
            );
    }

    #[Test]
    public function index_他人の商品の在庫履歴は表示されない(): void
    {
        $other = Owner::factory()->withShop()->create();
        $othersItem = Item::factory()->for($other->shop)->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.stocks.index', ['item' => $othersItem]));

        $res->assertForbidden();
    }
    
    #[Test]
    public function create_在庫登録画面を表示できる(): void
    {
        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.stock.create', ['item' => $this->item]));
        
        $res->assertOk()
            ->assertViewIs('owner.stocks.create')
            ->assertViewHas('item');
    }

    #[Test]
    public function create_他人の商品の在庫登録画面は表示されない(): void
    {
        $other = Owner::factory()->withShop()->create();
        $othersItem = Item::factory()->for($other->shop)->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.stock.create', ['item' => $othersItem]));

        $res->assertForbidden();
    }

    #[Test]
    public function store_在庫を登録し商品一覧にリダイレクトする(): void
    {
        $req = [
            'stock_diff' => 10,
            'up_down' => 1,
            'reason' => '入荷'
        ];

        $mock = Mockery::mock(StockService::class);
        $mock->shouldReceive('storeStockAndHistory')->once();
        $this->app->instance(StockService::class, $mock);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->post(route('owner.item.stock.store', ['item' => $this->item]), $req);

        $res->assertRedirect(route('owner.item.index'))
            ->assertSessionHas(['status', 'message']);
    }
    #[Test]
    public function store_登録処理で例外発生し登録画面に戻る(): void
    {
        $req = [
            'stock_diff' => 10,
            'up_down' => 1,
            'reason' => '入荷'
        ];

        $mock = Mockery::mock(StockService::class);
        $mock->shouldReceive('storeStockAndHistory')->once()->andThrow(new \Exception('error'));
        $this->app->instance(StockService::class, $mock);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->from(route('owner.item.stock.create', ['item' => $this->item]))
            ->post(route('owner.item.stock.store', ['item' => $this->item]), $req);

        $res->assertRedirect(route('owner.item.stock.create', ['item' => $this->item]))
            ->assertSessionHas(['status', 'message']);
    }

    #[Test]
    public function store_他人の商品の在庫登録はできない(): void
    {
        $req = [
            'stock_diff' => 10,
            'up_down' => 1,
        ];
        $other = Owner::factory()->withShop()->create();
        $othersItem = Item::factory()->for($other->shop)->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.stock.store', ['item' => $othersItem]));

        $res->assertForbidden();
    }

    #[Test]
    public function showUploadForm_アップロード画面を表示する():void
    {
        $res = $this->actingAs($this->owner, 'web_owner')->get(route('owner.stocks.csv.create'));

        $res->assertOk()
            ->assertViewIs('owner.stocks.upload');
    }
    #[Test]
    public function showUploadForm_商品登録がないと商品登録画面へリダイレクト():void
    {
        $other = Owner::factory()->withShop()->create();
        $res = $this->actingAs($other, 'web_owner')->get(route('owner.stocks.csv.create'));

        $res->assertRedirect(route('owner.item.create'))
            ->assertSessionHas(['status', 'message']);
    }

    #[Test]
    public function storeFromCsv_アップロード完了が成功したら一覧画面へ戻り件数を表示(): void
    {
        $file = UploadedFile::fake()->create('test.csv');
        $mock = Mockery::mock(StockCsvImportService::class);
        $mock->shouldReceive('import')->once()->andReturn(2);
        $this->app->instance(StockCsvImportService::class, $mock);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->post(route('owner.stocks.csv.store'), [
            'csv' => $file,
        ]);

        $res->assertRedirect(route('owner.item.index'))
            ->assertSessionHas('status')
            ->assertSessionHas([
                'status' => 'info',
                'message' => 'CSVファイルから 2件 の在庫データをアップロードしました'
            ]);
    }

    #[Test]
    public function storeFromCsv_アップロード失敗で戻る(): void
    {
        $file = UploadedFile::fake()->create('test.csv');
        $mock = Mockery::mock(StockCsvImportService::class);
        $mock->shouldReceive('import')->andThrow(new \Exception('upload-error'));
        $this->app->instance(StockCsvImportService::class, $mock);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->from(route('owner.stocks.csv.create'))
            ->post(route('owner.stocks.csv.store'), [
                'csv' => $file
            ]);

        $res->assertRedirect(route('owner.stocks.csv.create'))
            ->assertSessionHasErrors('csv');
    }


    private function createHistory(int $days): StockHistory
    {
        return StockHistory::factory()->for($this->item)->create([
            'created_at' => now()->subDays($days)
        ]);
    }
}
