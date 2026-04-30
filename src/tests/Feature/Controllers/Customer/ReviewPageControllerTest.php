<?php

namespace Tests\Feature\Controllers\Customer;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewPageControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_正しくviewを表示する(): void
    {
        $item = Item::factory()->create();

        $response = $this->get(route('item.reviews', ['item' => $item]));

        $response->assertOk()
            ->assertViewIs('user.reviews.index')
            ->assertViewHas('itemId');
    }
}
