<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use XPay\Laravel\Facades\XPay;
use XPay\Types\PaymentMethodData;
use XPay\Types\PaymentRequest;

class PaymentController extends Controller
{
    /**
     * Create a new payment
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'currency' => 'sometimes|string|size:3',
            'description' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email',
            'success_url' => 'sometimes|url',
            'cancel_url' => 'sometimes|url',
        ]);

        try {
            // Prepare payment method data based on payment method
            $paymentMethodData = match ($request->payment_method) {
                'stripe' => new PaymentMethodData(
                    paymentMethodTypes: ['card']
                ),
                'momo', 'momo_liberia', 'momo_nigeria', 'momo_uganda', 'momo_rwanda' => new PaymentMethodData(
                    phoneNumber: $request->phone_number
                ),
                'xpay_wallet' => new PaymentMethodData(
                    walletId: $request->wallet_id,
                    pin: $request->pin
                ),
                default => null
            };

            $payment = XPay::payments()->create(new PaymentRequest(
                amount: (string) $request->amount,
                paymentMethod: $request->payment_method,
                currency: $request->currency,
                description: $request->description,
                paymentMethodData: $paymentMethodData,
                successUrl: $request->success_url,
                cancelUrl: $request->cancel_url,
                metadata: [
                    'customer_email' => $request->customer_email,
                    'created_from' => 'laravel_app'
                ]
            ));

            return response()->json([
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'client_secret' => $payment->clientSecret,
                    'reference_id' => $payment->referenceId,
                    'transaction_url' => $payment->transactionUrl,
                    'instructions' => $payment->instructions,
                ]
            ]);

        } catch (\XPay\Exceptions\XPayException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ], $e->getHttpStatus() ?? 500);
        }
    }

    /**
     * Retrieve a payment
     */
    public function show(string $paymentId): JsonResponse
    {
        try {
            $payment = XPay::payments()->retrieve($paymentId);

            return response()->json([
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'description' => $payment->description,
                    'payment_method' => $payment->paymentMethod,
                    'created_at' => $payment->createdAt?->toISOString(),
                    'updated_at' => $payment->updatedAt?->toISOString(),
                ]
            ]);

        } catch (\XPay\Exceptions\XPayException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ], $e->getHttpStatus() ?? 500);
        }
    }

    /**
     * List payments
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $params = array_filter([
                'limit' => $request->integer('limit', 20),
                'offset' => $request->integer('offset', 0),
                'status' => $request->string('status')->value(),
                'customer_id' => $request->string('customer_id')->value(),
            ]);

            $result = XPay::payments()->list($params);

            return response()->json([
                'success' => true,
                'payments' => array_map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'status' => $payment->status,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'description' => $payment->description,
                        'payment_method' => $payment->paymentMethod,
                        'created_at' => $payment->createdAt?->toISOString(),
                    ];
                }, $result['payments']),
                'total' => $result['total']
            ]);

        } catch (\XPay\Exceptions\XPayException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ], $e->getHttpStatus() ?? 500);
        }
    }

    /**
     * Get available payment methods
     */
    public function paymentMethods(): JsonResponse
    {
        try {
            $methods = XPay::getPaymentMethods();

            return response()->json([
                'success' => true,
                'payment_methods' => $methods['payment_methods'],
                'environment' => $methods['environment']
            ]);

        } catch (\XPay\Exceptions\XPayException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ], $e->getHttpStatus() ?? 500);
        }
    }
}