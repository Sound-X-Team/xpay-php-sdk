<?php

declare(strict_types=1);

namespace XPay\Resources;

use XPay\Http\Client;
use XPay\Types\ApiResponse;
use XPay\Types\Payment;
use XPay\Types\PaymentRequest;
use XPay\Utils\CurrencyUtils;

final class Payments
{
    public function __construct(
        private readonly Client $client,
        private readonly string $merchantId
    ) {
    }

    /**
     * Create a new payment
     */
    public function create(PaymentRequest $paymentData): Payment
    {
        // Process the payment data
        $processedData = $this->processPaymentRequest($paymentData);

        $response = $this->client->post(
            "/v1/api/merchants/{$this->merchantId}/payments",
            $processedData
        );

        return Payment::fromArray($response->data);
    }

    /**
     * Retrieve a payment by ID
     */
    public function retrieve(string $paymentId): Payment
    {
        $response = $this->client->get("/v1/api/merchants/{$this->merchantId}/payments/{$paymentId}");

        return Payment::fromArray($response->data);
    }

    /**
     * List all payments
     */
    public function list(?array $params = null): array
    {
        $queryParams = [];
        
        if ($params !== null) {
            $allowedParams = ['limit', 'offset', 'status', 'customer_id', 'created_after', 'created_before'];
            
            foreach ($allowedParams as $param) {
                if (isset($params[$param])) {
                    $queryParams[$param] = (string) $params[$param];
                }
            }
        }

        $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
        $endpoint = "/v1/api/merchants/{$this->merchantId}/payments{$queryString}";
        
        $response = $this->client->get($endpoint);

        return [
            'payments' => array_map(
                fn(array $payment) => Payment::fromArray($payment),
                $response->data['payments'] ?? []
            ),
            'total' => $response->data['total'] ?? 0,
        ];
    }

    /**
     * Cancel a payment (if supported by payment method)
     */
    public function cancel(string $paymentId): Payment
    {
        $response = $this->client->post("/v1/api/merchants/{$this->merchantId}/payments/{$paymentId}/cancel");

        return Payment::fromArray($response->data);
    }

    /**
     * Confirm a payment (for payment methods that require confirmation)
     */
    public function confirm(string $paymentId, ?array $confirmationData = null): Payment
    {
        $response = $this->client->post(
            "/v1/payments/{$paymentId}/confirm",
            $confirmationData
        );

        return Payment::fromArray($response->data);
    }

    /**
     * Get supported currencies for a payment method
     */
    public function getSupportedCurrencies(string $paymentMethod): array
    {
        return CurrencyUtils::getSupportedCurrencies($paymentMethod);
    }

    /**
     * Convert amount to smallest currency unit (e.g., dollars to cents)
     */
    public static function toSmallestUnit(float $amount, string $currency): int
    {
        return CurrencyUtils::toSmallestUnit($amount, $currency);
    }

    /**
     * Convert amount from smallest currency unit (e.g., cents to dollars)
     */
    public static function fromSmallestUnit(int $amount, string $currency): float
    {
        return CurrencyUtils::fromSmallestUnit($amount, $currency);
    }

    /**
     * Format amount for display with currency symbol
     */
    public static function formatAmount(float $amount, string $currency, bool $fromSmallestUnit = true): string
    {
        return CurrencyUtils::formatAmount($amount, $currency, $fromSmallestUnit);
    }

    private function processPaymentRequest(PaymentRequest $paymentData): array
    {
        $processedData = $paymentData->toArray();

        // Auto-assign currency if not provided
        if ($paymentData->currency === null) {
            $processedData['currency'] = CurrencyUtils::getDefaultCurrency($paymentData->paymentMethod);
        }

        // Validate currency for the payment method
        CurrencyUtils::validateCurrency(
            $paymentData->paymentMethod, 
            $processedData['currency']
        );

        return $processedData;
    }
}