<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

/**
 * Server-to-server backup for subscription activation — the browser
 * redirect in GetStartedController@verifyPayment handles the common case,
 * but a closed tab or a network blip there would otherwise leave a paid
 * subscription stuck "incomplete" with no way to know. This is the one
 * path Razorpay guarantees will fire regardless of what the browser does.
 */
class RazorpayWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $signature = $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();

        try {
            (new Api(config('services.razorpay.key'), config('services.razorpay.secret')))
                ->utility->verifyWebhookSignature($payload, $signature, config('services.razorpay.webhook_secret'));
        } catch (SignatureVerificationError $e) {
            Log::warning('Rejected a Razorpay webhook with an invalid signature.');

            return response('Invalid signature', 400);
        }

        $event = $request->input('event');

        if ($event === 'payment.captured') {
            $this->handlePaymentCaptured($request);
        }

        return response('OK', 200);
    }

    private function handlePaymentCaptured(Request $request): void
    {
        $payment = $request->input('payload.payment.entity', []);
        $paymentId = $payment['id'] ?? null;
        $orderId = $payment['order_id'] ?? null;

        if (! $paymentId || ! $orderId) {
            return;
        }

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $order = $api->order->fetch($orderId);
        $subscriptionId = $order['notes']['subscription_id'] ?? null;

        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::find($subscriptionId);

        if (! $subscription) {
            return;
        }

        app(GetStartedController::class)->activateSubscription($subscription, $paymentId, $orderId);
    }
}
