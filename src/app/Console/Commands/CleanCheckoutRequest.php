<?php

namespace App\Console\Commands;

use App\Enums\CheckoutStatus;
use App\Models\CheckoutRequest;
use App\Services\Customer\Order\WebhookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


/**
 * Stripe_webhookのexpiredイベント処理のフォールバック
 * （途中離脱の処理が失敗した際のロールバック）
 */
class CleanCheckoutRequest extends Command
{
    public function __construct(private WebhookService $webhookService)
    {
        parent::__construct();
    }

    protected $signature = 'app:clean-checkout-request';
    protected $description = 'cleanup-expired-checkout';

    /**
     * ステータスが期限切れとなっているレコードを取得し
     * ステータスの変更と在庫戻しを実行、結果をlack通知 
     */
    public function handle()
    {
        try {
            $query = CheckoutRequest::query()
                ->with('checkoutItems')
                ->where('status', CheckoutStatus::PENDING)
                ->where('expires_at', '<', now());

            $expiredCount = 0;

            $query->chunkById(100, function ($expiredRequests) use (&$expiredCount) {
                foreach ($expiredRequests as $expiredRequest) {
                    $result = $this->webhookService->expireOrFailCheckout(
                        $expiredRequest, CheckoutStatus::EXPIRED, 'cron-batch'
                    );
    
                    if ($result !== null) $expiredCount++;
                }
            });

            $exitCode = Command::SUCCESS;
            $message = "*Laravel ECサイトV2 CleanExpiredRequest Report {$expiredCount}件*\n" . "Status: ✅ SUCCESS\n";

        } catch (\Throwable $e) {
            Log::error('clean-checkout-request error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            $exitCode = Command::FAILURE;
            $message = "*Laravel ECサイトV2 CleanExpiredRequest Report*\n" .
                "Status: ⚠️ WARNINGS / ERRORS\n" .
                "```{$e->getMessage()}```";
        }

        try {
            Http::retry(3, 200)
                ->timeout(5)
                ->asJson()
                ->post(config('services.slack.webhook_url'), ['text' => $message]);

        } catch (\Throwable $s) {
            Log::warning('Slack notification failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $exitCode;
    }
}
