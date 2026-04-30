<?php
namespace App\Services\Customer\Order;

use App\Enums\CheckoutStatus;
use App\Models\CheckoutItem;
use App\Models\CheckoutRequest;
use App\Models\WebhookEvent;
use App\Services\Customer\Cart\CartServiceInterface;
use App\Services\Customer\Order\DTO\FailedOrderDto;
use App\Services\Customer\Order\DTO\OrderProcessResultDto;
use App\Services\Customer\Order\Exceptions\NotFoundCheckoutRequestException;
use App\Services\Customer\Order\Notification\OrderNotificationServiceInterface;
use App\Services\Customer\Shipment\ShipmentServiceInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Webhook;

class WebhookService implements WebhookServiceInterface
{
    public function __construct(
        private OrderServiceInterface $orderService,
        private ShipmentServiceInterface $shipmentService,
        private CartServiceInterface $cartService,
        private CheckoutServiceInterface $checkoutService,
        private OrderNotificationServiceInterface $orderNotificationService
    )
    {
    }

    /** 署名検証 */
    public function verifyWebhook(string $payload, string $sigHeader): Event
    {
        return Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.webhook.secret')
        );
    }

    public function handle(Event $event): void
    {
        switch ($event->type) {

            case 'checkout.session.completed':
                $result = $this->handleCompleted($event);
                if ($result !== null) {
                    $this->orderNotificationService->notifyCustomer($result);
                    $this->orderNotificationService->notifyOwners($result);
                }
                break;

            case 'checkout.session.expired':
                $result = $this->handleExpiredAndFailed($event, CheckoutStatus::EXPIRED);
                if ($result !== null) {
                    $this->orderNotificationService->notifyCheckoutExpired($result);
                }
                break;

            case 'payment_intent.payment_failed':
                $result = $this->handleExpiredAndFailed($event, CheckoutStatus::FAILED);
                if ($result !== null) {
                    $this->orderNotificationService->notifyPaymentFailed($result);
                }
                break;
            
            default:
                return;
        }
    }

    /**
     * 期限切れと決済失敗処理のコア部分(ステータス変更と在庫戻し関連処理)
     *  webhookイベントを受け取る・期限切れのcron(バッチ)処理　の双方で利用する為にコア部分を切り出し
     */
    public function expireOrFailCheckout(
        CheckoutRequest $checkoutRequest,
        CheckoutStatus $status,
        string $reason
    ):  ?FailedOrderDto
    {
        if ($checkoutRequest->status !== CheckoutStatus::PENDING) return null;

        $checkoutItems = $checkoutRequest->checkoutItems;
        if ($checkoutItems === null) return null;

        $this->checkoutService->rollbackCheckout(
            $checkoutItems,
            $status,
            $reason, // webhook:$event->type || cron: "cron-batch"
        );

        return new FailedOrderDto($checkoutRequest, $checkoutItems);
    }

    /** 
     * webhook成功時の処理
     * < orders / shipments / order_items の新規登録 / checkout_requests ステータス変更 >
     */
    private function handleCompleted(Event $event): ?OrderProcessResultDto
    {
        $checkoutRequest = $this->getCheckoutRequestAndCheckStatus($event);
        if (! $checkoutRequest) return null;

        $result = DB::transaction(function () use ($event, $checkoutRequest) {
            // 二重登録防止
            try {
                $this->markProcessed($event);

            } catch (QueryException $e) {
                return null;
            }

            $order = $this->orderService->createOrder($checkoutRequest, $event);
            $shipments = $this->shipmentService->createShipmentAndOrderItem($checkoutRequest, $order);
            $this->checkoutService->updateStatus($checkoutRequest, CheckoutStatus::COMPLETED);
            $this->cartService->deleteFromCheckout($checkoutRequest);

            return new OrderProcessResultDto($order, $shipments);
        });
        
        return $result;
    }

    /** 
     * 決済失敗時の処理(期限切れと決済失敗 共通)
     * webhook_events登録・在庫戻し・在庫履歴登録・checkoutステータス変更
     */
    private function handleExpiredAndFailed(Event $event, CheckoutStatus $status): ?FailedOrderDto
    {
        $checkoutRequest = $this->getCheckoutRequestAndCheckStatus($event);
        if (! $checkoutRequest) return null;

        try {
            $this->markProcessed($event);

        } catch (QueryException $e) {
            return null;
        }

        return $this->expireOrFailCheckout($checkoutRequest, $status, $event->type);
    }

    /** event新規登録 */
    private function markProcessed(Event $event): void
    {
        WebhookEvent::create([
            'event_id' => $event->id,
            'type' => $event->type,
            'processed_at' => now(),
        ]);
    }

    /**
     * checkoutRequest取得とステータスチェック（二重処理防止）
     */
    private function getCheckoutRequestAndCheckStatus(Event $event): ?CheckoutRequest
    {
        $checkoutId = $event->data->object->metadata->checkout_id ?? null;
        if (! $checkoutId) {
            Log::channel('stripe')->warning('checkout_idが未送信', [
                'event_id' => $event->id,
                'type' => $event->type,
            ]);
            return null;
        }

        $checkoutRequest = CheckoutRequest::with('checkoutItems')->find($checkoutId);
        if (! $checkoutRequest) {
            throw new NotFoundCheckoutRequestException();
        } 

        if ($checkoutRequest->status !== CheckoutStatus::PENDING) {
            Log::channel('stripe')->info('二重送信の為、スキップ', [
                'checkout_id' => $checkoutId,
                'status' => $checkoutRequest->status,
            ]);
            return null;
        }

        return $checkoutRequest;
    }

}