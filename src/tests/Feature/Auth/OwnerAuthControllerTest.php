<?php

namespace Tests\Feature\Auth;

use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OwnerAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function loginForm_オーナー用のログインフォームを表示(): void
    {
        $res = $this->get(route('owner.login'));

        $res->assertOk();
        $res->assertViewIs('auth.owner.login');
    }

    #[Test]
    public function login_オーナーとしてログイン(): void
    {
        $owner = Owner::factory()->create([
            'password' => Hash::make('password')
        ]);

        $res = $this->post(route('owner.login.submit'), [
            'email' => $owner->email,
            'password' => 'password'
        ]);

        $res->assertRedirect(route('owner.shop.index'));
        $this->assertAuthenticatedAs($owner, 'web_owner');
    }
    
    #[Test]
    public function login_オーナー登録していないとエラー(): void
    {
        $res = $this->post(route('owner.login.submit'), [
            'email' => 'user@mail.com',
            'password' => 'password'
        ]);

        $res->assertStatus(302);
        $res->assertSessionHasErrors('email');
    }

    #[Test]
    public function login_ログイン済の場合はホーム画面にリダイレクト(): void
    {
        $owner = Owner::factory()->create();
        
        $res = $this->actingAs($owner, 'web_owner')->get(route('owner.login'));

        $res->assertRedirect(route('owner.shop.index'));
    }
    #[Test]
    public function login_ログイン時のバリデーションエラー(): void
    {
        $owner = Owner::factory()->create([
            'password' => Hash::make('password')
        ]);

        $res = $this->post(route('owner.login.submit'), [
            'email' => 'invalid@mail.com',
            'password' => 'password'
        ]);

        $res->assertRedirectBack();
        $res->assertSessionHasErrors('email');
        $this->assertGuest('web_owner');
    }

    #[Test]
    public function logout_認証済オーナーはログアウトできる(): void
    {
        $owner = Owner::factory()->create();

        $res = $this->actingAs($owner, 'web_owner')->post(route('owner.logout'));

        $res->assertRedirect(route('owner.login'));
        $this->assertGuest('web_owner');
    }
}
