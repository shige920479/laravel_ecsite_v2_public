<?php

namespace Tests\Feature\Middleware;

use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedirectIfAuthenticatedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function handle_オーナーでログイン中に正しくリダイレクト(): void
    {
    $owner = Owner::factory()->create();

    $res = $this->actingAs($owner, 'web_owner')->get(route('owner.login'));

    $res->assertRedirect(route('owner.shop.index'));
    }

    #[Test]
    public function handle_未ログインの場合はログイン画面が表示される(): void
    {
        $res = $this->get(route('owner.login'));

        $res->assertOk();
        $res->assertViewIs('auth.owner.login');
    }
}
