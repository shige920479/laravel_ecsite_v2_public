<?php

namespace Tests\Feature\Controllers\Customer;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_指定のビューを表示する(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'web')->get(route('favorite.index'));

        $response->assertViewIs('user.favorite.index');
    }
}
