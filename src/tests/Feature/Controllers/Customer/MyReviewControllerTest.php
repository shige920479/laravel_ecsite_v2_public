<?php

namespace Tests\Feature\Controllers\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MyReviewControllerTest extends TestCase
{
 
    use RefreshDatabase;

    #[Test]
    public function index_指定のビューを表示する(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->get(route('mypage.reviews.index'));

        $response->assertOk()
            ->assertViewIs('user.mypage.my-review');
    }

}
