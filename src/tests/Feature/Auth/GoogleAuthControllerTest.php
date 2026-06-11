<?php

namespace Tests\Feature\Auth;

use App\Events\GoogleAccountLinked;
use App\Events\GoogleLoginCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use App\Models\User;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as TwoUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    #[Test]
    public function callback_新規にGoogle認証でログインできる(): void
    {
        $googleUser = $this->getGoogleUser('google-123', 'test@gmail.com');

        $this->buildGoogleMock($googleUser);
        
        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com',
            'google_id' => 'google-123',
        ]);

        Event::assertDispatched(GoogleAccountLinked::class);
        Event::assertDispatched(GoogleLoginCompleted::class);
    }

    #[Test]
    public function callback_既存のGmailアドレスに認証連携できる(): void
    {
        $existingUser = User::factory()->create(['email' => 'test@gmail.com', 'google_id' => null]);

        $googleUser = $this->getGoogleUser('google-123', 'test@gmail.com');

        $this->buildGoogleMock($googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home.index'));

        $this->assertAuthenticatedAs($existingUser, 'web');

        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com',
            'google_id' => 'google-123',
        ]);

        Event::assertDispatched(GoogleAccountLinked::class);
        Event::assertDispatched(GoogleLoginCompleted::class);
    }
    #[Test]
    public function callback_連携済みのGmailアドレスでGoogle認証できる(): void
    {
        $existingUser = User::factory()->create(['email' => 'test@gmail.com', 'google_id' => 'google-123']);

        $googleUser = $this->getGoogleUser('google-123', 'test@gmail.com');

        $this->buildGoogleMock($googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home.index'));

        $this->assertAuthenticatedAs($existingUser, 'web');

        Event::assertNotDispatched(GoogleAccountLinked::class);
        Event::assertDispatched(GoogleLoginCompleted::class);
    }

    #[Test]
    public function callback_google認証エラーで例外を投げログイン画面へリダイレクト(): void
    {
        $googleUser = $this->getGoogleUser('google-123', 'test@gmail.com');
        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andThrow(new Exception('google-error'));
        
        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'))
            ->assertSessionHas(['message' => 'Google認証に失敗しました。再度お試しください']);
    }

    #[Test]
    public function callback_google_idがミスマッチの場合は例外を投げログイン画面へリダイレクト(): void
    {
        User::factory()->create(['email' => 'test@gmail.com', 'google_id' => 'google-777']);
        $googleUser = $this->getGoogleUser('google-123', 'test@gmail.com');
        $this->buildGoogleMock($googleUser);
        
        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'))
            ->assertSessionHas(['message' => 'Googleアカウントが一致していません。連携済みのGoogleアカウントでログインしてください。']);
    }

    private function getGoogleUser(string $googleId, string $email): TwoUser
    {
        $googleUser = new TwoUser();
        
        $googleUser->id = $googleId;
        $googleUser->name = 'test';
        $googleUser->email = $email;

        return $googleUser;
    }

    private function buildGoogleMock(TwoUser $googleUser): void
    {
        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturnSelf();
        
        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($googleUser);
    }
}
