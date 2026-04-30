<?php

namespace Tests\Feature\Requests;

use App\Models\Item;
use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateItemRequestTest extends TestCase
{
    use RefreshDatabase;

    private Owner $owner;
    private Owner $other;
    private Item $item1;
    private Item $item2;
    private Item $otherItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = Owner::factory()->withShop()->create();
        $this->other = Owner::factory()->withShop()->create();
        $this->item1 = Item::factory()->for($this->owner->shop)->create(['name' => 'test1']);
        $this->item2 = Item::factory()->for($this->owner->shop)->create(['name' => 'test2']);
        $this->otherItem = Item::factory()->for($this->other->shop)->create(['name' => 'dummy1']);
    }

    #[Test]
    public function rule_name_自分の商品の中では重複を許さない(): void
    {
        $updated = $this->item1->toArray();
        $updated['name'] = 'test2';
        
        $res = $this->actingAs($this->owner, 'web_owner')
            ->post(route('owner.item.updateConfirm', ['item' => $this->item1]), $updated);

        $res->assertStatus(302)
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function rule_name_他人の商品とは重複を許す(): void
    {
        $updated = $this->item1->toArray();
        $updated['name'] = 'dummy1';
        
        $res = $this->actingAs($this->owner, 'web_owner')
            ->post(route('owner.item.updateConfirm', ['item' => $this->item1]), $updated);

        $res->assertOk()
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function rule_name_元の名前とは重複を許す(): void
    {
        $updated = $this->item1->toArray();
        $updated['name'] = 'test1';
        $updated['price_ex_tax'] = (int)$updated['price_ex_tax'] + 100;
        
        $res = $this->actingAs($this->owner, 'web_owner')
            ->post(route('owner.item.updateConfirm', ['item' => $this->item1]), $updated);

        $res->assertOk()
            ->assertSessionHasNoErrors();
    }


}
