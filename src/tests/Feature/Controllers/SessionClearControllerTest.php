<?php

namespace Tests\Feature\Controllers;

use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SessionClearControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function clear_セッションを破棄してリダイレクトする(): void
    {
        $owner = Owner::factory()->create();
        $req = ['route' => 'owner.item.index'];
        session(['tmp_item_image.1' => "tmp/{$owner->id}/test.webp"]);

        $res = $this->actingAs($owner, 'web_owner')
            ->post(route('owner.session.clear'), $req);

        $res->assertRedirect(route('owner.item.index'))
            ->assertSessionMissing('tmp_item_image.1');
    }

    // 続きはバリデーションエラーになるパターン（ルート違い　404）
    #[Test]
    public function clear_ホワイトリスト以外のルートを指定して404エラー(): void
    {
        $owner = Owner::factory()->create();
        $req = ['route' => 'owner.dummy'];

        $res = $this->actingAs($owner, 'web_owner')
            ->post(route('owner.session.clear'), $req);
        
        $res->assertNotFound();
    }
}
