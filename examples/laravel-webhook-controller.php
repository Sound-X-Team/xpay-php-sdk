<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle X-Pay webhooks
     * 
     * Make sure to add the VerifyWebhookSignature middleware to your routes:
     * Route::post('/webhooks/xpay', [WebhookController::class, 'handle'])
     *     ->middleware('xpay.webhook');
     */
    public function handle(Request $request): Response
    {
        // The webhook data is already parsed and validated by the middleware
        $webhookData = $request->input('webhook_data');
        
        Log::info('X-Pay webhook received', [
            'event_id' => $webhookData['id'],
            'event_type' => $webhookData['type']
        ]);

        try {
            match ($webhookData['type']) {
                'payment.created' => $this->handlePaymentCreated($webhookData['data']),
                'payment.succeeded' => $this->handlePaymentSucceeded($webhookData['data']),
                'payment.failed' => $this->handlePaymentFailed($webhookData['data']),
                'payment.cancelled' => $this->handlePaymentCancelled($webhookData['data']),
                'payment.refunded' => $this->handlePaymentRefunded($webhookData['data']),
                'customer.created' => $this->handleCustomerCreated($webhookData['data']),
                'customer.updated' => $this->handleCustomerUpdated($webhookData['data']),
                default => Log::warning('Unhandled webhook event', ['type' => $webhookData['type']])
            };

            return response('OK', 200);

        } catch (\Throwable $e) {
            Log::error('Webhook processing failed', [
                'event_id' => $webhookData['id'],
                'error' => $e->getMessage()
            ]);

            return response('Internal Server Error', 500);
        }
    }

    private function handlePaymentCreated(array $data): void
    {
        $payment = $data['payment'];
        
        Log::info('Payment created', [
            'payment_id' => $payment['id'],
            'amount' => $payment['amount'],
            'currency' => $payment['currency']
        ]);

        // Add your business logic here
        // For example: Update order status, send notification email, etc.
    }

    private function handlePaymentSucceeded(array $data): void
    {
        $payment = $data['payment'];
        
        Log::info('Payment succeeded', [
            'payment_id' => $payment['id'],
            'amount' => $payment['amount'],
            'currency' => $payment['currency']
        ]);

        // Add your business logic here
        // For example: Fulfill order, send confirmation email, update inventory, etc.
    }

    private function handlePaymentFailed(array $data): void
    {
        $payment = $data['payment'];
        
        Log::warning('Payment failed', [
            'payment_id' => $payment['id'],
            'amount' => $payment['amount'],
            'currency' => $payment['currency']
        ]);

        // Add your business logic here
        // For example: Cancel order, send failure notification, etc.
    }

    private function handlePaymentCancelled(array $data): void
    {
        $payment = $data['payment'];
        
        Log::info('Payment cancelled', [
            'payment_id' => $payment['id'],
            'amount' => $payment['amount'],
            'currency' => $payment['currency']
        ]);

        // Add your business logic here
    }

    private function handlePaymentRefunded(array $data): void
    {
        $payment = $data['payment'];
        $refund = $data['refund'] ?? null;
        
        Log::info('Payment refunded', [
            'payment_id' => $payment['id'],
            'refund_id' => $refund['id'] ?? null,
            'amount' => $refund['amount'] ?? $payment['amount'],
            'currency' => $payment['currency']
        ]);

        // Add your business logic here
    }

    private function handleCustomerCreated(array $data): void
    {
        $customer = $data['customer'];
        
        Log::info('Customer created', [
            'customer_id' => $customer['id'],
            'email' => $customer['email']
        ]);

        // Add your business logic here
    }

    private function handleCustomerUpdated(array $data): void
    {
        $customer = $data['customer'];
        
        Log::info('Customer updated', [
            'customer_id' => $customer['id'],
            'email' => $customer['email']
        ]);

        // Add your business logic here
    }
}

// Add this to your routes/web.php or routes/api.php:
/*
use App\Http\Controllers\WebhookController;
use XPay\Laravel\Middleware\VerifyWebhookSignature;

Route::post('/webhooks/xpay', [WebhookController::class, 'handle'])
    ->middleware(VerifyWebhookSignature::class);
*/