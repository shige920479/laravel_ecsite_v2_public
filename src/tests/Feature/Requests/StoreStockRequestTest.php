<?php

namespace Tests\Feature\Requests;

use App\Models\Item;
use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreStockRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function after_在庫数を越えていたらエラー判定(): void
    {
        $owner = Owner::factory()->withShop()->create();
        $item = Item::factory()->for($owner->shop)->create(['stock_current' => 5]);

        $req = [
            'stock_diff' => 10,
            'up_down' => 0,
        ];

        $res = $this->actingAs($owner, 'web_owner')
            ->post(route('owner.item.stock.store', ['item' => $item]), $req);

        $res->assertSessionHasErrors('stock_diff');
    }
}
