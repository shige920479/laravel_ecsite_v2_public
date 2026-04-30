<?php

namespace Tests\Feature\Services\Customer;

use App\Models\CheckoutRequest;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Shop;
use App\Models\User;
use App\Notifications\Order\CheckoutExpiredNotification;
use App\Notifications\Order\OrderCompletedForCustomerNotification;
use App\Notifications\Order\OrderReceivedForOwnerNotification;
use App\Notifications\Order\PaymentFailedNotification;
use App\Services\Customer\Order\DTO\FailedOrderDto;
use App\Services\Customer\Order\DTO\OrderProcessResultDto;
use App\Services\Customer\Order\DTO\ShipmentResultDto;
use App\Services\Customer\Order\Notification\OrderNotificationService;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function notifyCustomer_カスタマーにメール通知を送信する(): void
    {
        Notification::fake();

        $user = User::factory()->registered()->create();
        $result = $this->fakeOrderResultDto($user);
        $order = $result->order;

        $service = app()->make(OrderNotificationService::class);
        $service->notifyCustomer($result);

        Notification::assertSentTo(
            $user,
            OrderCompletedForCustomerNotification::class,
            function ($notification) use ($order) {
                return $notification->result->order->id === $order->id;
            }
        );
    }
    #[Test]
    public function notifyOwners_オーナー毎にメールを通知する(): void
    {
        Notification::fake();

        $user = User::factory()->registered()->create();
        $result = $this->fakeOrderResultDto($user);
        $shipmentResults = $result->shipmentResults;

        $owner1 = $shipmentResults[0]->owner();
        $owner2 = $shipmentResults[1]->owner();

        $service = app()->make(OrderNotificationService::class);
        $service->notifyOwners($result);

        Notification::assertSentTo(
            $owner1,
            OrderReceivedForOwnerNotification::class
        );
        Notification::assertSentTo(
            $owner2,
            OrderReceivedForOwnerNotification::class
        );
        Notification::assertSentTimes(
            OrderReceivedForOwnerNotification::class,
            2
        );
    }
    #[Test]
    public function notifyCheckoutExpired_カスタマーに期限切れを通知する(): void
    {
        Notification::fake();
        $user = User::factory()->registered()->create();
        $result = $this->fakeFailedResultDto($user);
        $checkoutId = $result->checkoutRequest->id;

        $service = app()->make(OrderNotificationService::class);
        $service->notifyCheckoutExpired($result);

        Notification::assertSentTo(
            $user,
            CheckoutExpiredNotification::class,
            function ($notification) use ($checkoutId) {
                return $notification->result->checkoutRequest->id === $checkoutId;
            }
        );
    }
    #[Test]
    public function notifyPaymentFailed_カスタマーに決済失敗を通知する(): void
    {
        Notification::fake();
        $user = User::factory()->registered()->create();
        $result = $this->fakeFailedResultDto($user);
        $checkoutId = $result->checkoutRequest->id;

        $service = app()->make(OrderNotificationService::class);
        $service->notifyPaymentFailed($result);

        Notification::assertSentTo(
            $user,
            PaymentFailedNotification::class,
            function ($notification) use ($checkoutId) {
                return $notification->result->checkoutRequest->id === $checkoutId;
            }
        );
    }

    private function fakeOrderResultDto(User $user): OrderProcessResultDto
    {
        $order = Order::factory()->for($user)->create();
        $shop1 = Shop::factory()->create();
        $shop2 = Shop::factory()->create();
        $shops = [$shop1, $shop2];

        $shipmentResults = collect();
        
        foreach ($shops as $shop) {
            $shipment = Shipment::factory()
                ->for($order)
                ->for($shop)
                ->create([
                    'shipping_name' => $user->name,
                    'shipping_postcode' => $user->postcode,
                    'shipping_address' => $user->address,
                    'shipping_phone' => $user->phone
                ]);
            
            $shipmentResultDto = new ShipmentResultDto(
                $shipment,
                collect()
            );
            $shipmentResults->push($shipmentResultDto);
        }

        return new OrderProcessResultDto(
            $order,
            $shipmentResults
        );
    }
    private function fakeFailedResultDto(User $user): FailedOrderDto
    {

        $checkoutRequest = CheckoutRequest::factory()->for($user)->create();
        $checkoutItems = collect([]);

        return new FailedOrderDto(
            $checkoutRequest,
            $checkoutItems
        );
    }
    



}
