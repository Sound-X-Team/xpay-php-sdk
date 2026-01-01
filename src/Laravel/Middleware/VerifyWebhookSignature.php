<?php

declare(strict_types=1);

namespace XPay\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use XPay\Utils\WebhookUtils;

final class VerifyWebhookSignature
{
  /**
   * Handle an incoming request.
   * 
   * @param \Illuminate\Http\Request $request
   * @param \Closure $next
   * @param string|null $secret
   * @return mixed
   */
  public function handle(Request $request, Closure $next, ?string $secret = null): mixed
  {
    $webhookSecret = $secret ?? \config('xpay.webhook.secret');

    if (!is_string($webhookSecret) || empty($webhookSecret)) {
      \abort(500, 'Webhook secret not configured');
    }

    if (!\config('xpay.webhook.verify_signature', true)) {
      return $next($request);
    }

    $signature = $request->header('X-XPay-Signature');

    if (!is_string($signature) || empty($signature)) {
      \abort(400, 'Missing webhook signature');
    }

    $payload = $request->getContent();

    if ($payload === false || !is_string($payload)) {
      \abort(400, 'Unable to read request payload');
    }

    if (!WebhookUtils::verifySignature($payload, $signature, $webhookSecret)) {
      \abort(401, 'Invalid webhook signature');
    }

    // Add parsed webhook data to request
    try {
      $webhookData = WebhookUtils::parseWebhookPayload($payload);

      if (!WebhookUtils::validateWebhookEvent($webhookData)) {
        \abort(400, 'Invalid webhook event structure');
      }

      $request->merge(['webhook_data' => $webhookData]);
    } catch (\InvalidArgumentException $e) {
      \abort(400, 'Invalid webhook payload: ' . $e->getMessage());
    } catch (\JsonException $e) {
      \abort(400, 'Invalid JSON in webhook payload: ' . $e->getMessage());
    }

    return $next($request);
  }
}
