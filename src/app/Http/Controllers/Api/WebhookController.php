<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Customer\Order\WebhookServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookServiceInterface $webhookService,
    )
    {
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');

        try {
            $event = $this->webhookService->verifyWebhook($payload, $sigHeader);

            $this->webhookService->handle($event);

        } catch (UnexpectedValueException | SignatureVerificationException $e) {
            Log::channel('stripe')->error('Webhook signature error', [$e]);

        } catch (\Throwable $e) {
            Log::channel('stripe')->warning('Webhook handle error', [
                'event_id' => isset($event) ? $event->id : null,
                'type' => isset($event) ? $event->type : null,
                'payload' => $payload,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } 
        
        return response()->json(['status' => 'ok'], 200);
    }
}
