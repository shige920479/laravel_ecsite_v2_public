<?php

namespace Tests\Feature\Requests;

use App\Models\Item;
use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreItemImageRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function after_画像が選択されていなければエラー判定(): void
    {
        session(['tmp_item_image' => '']);
        $owner = Owner::factory()->withShop()->create();
        $item = Item::factory()->for($owner->shop)->create();

        $res = $this->actingAs($owner, 'web_owner')
            ->from(route('owner.item.image.create', ['item' => $item]))
            ->post(route('owner.item.image.store', ['item' => $item]));

        $res->assertRedirect(route('owner.item.image.create', ['item' => $item]))
            ->assertSessionHasErrors('image');
    }

}
