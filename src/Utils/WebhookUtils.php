<?php

declare(strict_types=1);

namespace XPay\Utils;

final class WebhookUtils
{
    /**
     * Verify webhook signature using HMAC-SHA256
     */
    public static function verifySignature(string $payload, string $signature, string $secret): bool
    {
        if (empty($payload) || empty($signature) || empty($secret)) {
            return false;
        }

        try {
            // Remove 'sha256=' prefix if present
            $expectedSignature = str_starts_with($signature, 'sha256=') 
                ? substr($signature, 7) 
                : $signature;

            // Compute HMAC-SHA256 signature
            $computedSignature = hash_hmac('sha256', $payload, $secret);

            // Use constant-time comparison to prevent timing attacks
            return hash_equals($computedSignature, $expectedSignature);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Generate webhook signature for testing
     */
    public static function generateSignature(string $payload, string $secret): string
    {
        return 'sha256=' . hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Parse webhook payload from JSON
     * 
     * @throws \InvalidArgumentException if payload is not valid JSON
     * @throws \JsonException if JSON parsing fails with JSON_THROW_ON_ERROR flag
     */
    public static function parseWebhookPayload(string $payload): array
    {
        if (empty($payload)) {
            throw new \InvalidArgumentException('Empty payload provided');
        }

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            
            if (!is_array($decoded)) {
                throw new \InvalidArgumentException('Webhook payload must be a JSON object');
            }
            
            return $decoded;
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Invalid JSON payload: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Validate webhook event structure
     * 
     * @param array<string, mixed> $event
     */
    public static function validateWebhookEvent(array $event): bool
    {
        $requiredFields = ['id', 'type', 'created_at', 'data'];
        
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $event)) {
                return false;
            }
        }
        
        // Validate that required fields have appropriate types
        if (!is_string($event['id']) || empty($event['id'])) {
            return false;
        }
        
        if (!is_string($event['type']) || empty($event['type'])) {
            return false;
        }
        
        if (!is_string($event['created_at']) || empty($event['created_at'])) {
            return false;
        }
        
        if (!is_array($event['data'])) {
            return false;
        }
        
        // Optionally validate that the event type is supported
        if (!self::isSupportedEvent($event['type'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Get supported webhook events
     * 
     * @return array<string>
     */
    public static function getSupportedEvents(): array
    {
        return [
            'payment.created',
            'payment.succeeded', 
            'payment.failed',
            'payment.cancelled',
            'payment.refunded',
            'refund.created',
            'refund.succeeded',
            'refund.failed',
            'customer.created',
            'customer.updated',
        ];
    }

    /**
     * Check if event type is supported
     */
    public static function isSupportedEvent(string $eventType): bool
    {
        return in_array($eventType, self::getSupportedEvents(), true);
    }
}