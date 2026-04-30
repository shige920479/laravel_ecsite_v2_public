<?php

namespace Tests\Feature\Controllers\Customer\Api;

use App\Services\Customer\Order\WebhookServiceInterface;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function handle_正常に処理し200を返す(): void
    {
        $mock = Mockery::mock(WebhookServiceInterface::class);
        $mock->shouldReceive('verifyWebhook')->once()->andReturn($this->createEvent());
        $mock->shouldReceive('handle')->once();
        $this->app->instance(WebhookServiceInterface::class, $mock);

        $response = $this->postJson(route('webhook.stripe'), [], [
            'stripe-signature' => 'test'
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    #[Test]
    public function handle_署名エラーで例外発生しても200を返す(): void
    {
        $mock = Mockery::mock(WebhookServiceInterface::class);
        $mock->shouldReceive('verifyWebhook')->once()
            ->andThrow(new SignatureVerificationException());
        $mock->shouldReceive('handle')->never();
        $this->app->instance(WebhookServiceInterface::class, $mock);

        $response = $this->postJson(route('webhook.stripe'), [], [
            'stripe-signature' => 'test'
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    #[Test]
    public function handle_handleエラーで例外発生しても200を返す(): void
    {
        $mock = Mockery::mock(WebhookServiceInterface::class);
        $mock->shouldReceive('verifyWebhook')->once()->andReturn($this->createEvent());
        $mock->shouldReceive('handle')->once()->andThrow(new Exception('handle-error'));
        $this->app->instance(WebhookServiceInterface::class, $mock);

        $response = $this->postJson(route('webhook.stripe'), [], [
            'stripe-signature' => 'test'
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    private function createEvent(): Event
    {
        return Event::constructFrom([
            'id' => 'evt_test',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => []
            ],
        ]);
    }
}
