<?php

declare(strict_types=1);

namespace XPay\Types;

final readonly class PaymentRequest
{
    public function __construct(
        public string $amount,
        public string $paymentMethod,
        public ?string $currency = null,
        public ?string $description = null,
        public ?string $customerId = null,
        public ?PaymentMethodData $paymentMethodData = null,
        public ?array $metadata = null,
        public ?string $successUrl = null,
        public ?string $cancelUrl = null,
        public ?string $webhookUrl = null
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'currency' => $this->currency,
            'description' => $this->description,
            'customer_id' => $this->customerId,
            'payment_method_data' => $this->paymentMethodData?->toArray(),
            'metadata' => $this->metadata,
            'success_url' => $this->successUrl,
            'cancel_url' => $this->cancelUrl,
            'webhook_url' => $this->webhookUrl,
        ], fn($value) => $value !== null);
    }
}

final readonly class PaymentMethodData
{
    public function __construct(
        public ?array $paymentMethodTypes = null,
        public ?string $phoneNumber = null,
        public ?string $walletId = null,
        public ?string $pin = null
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'payment_method_types' => $this->paymentMethodTypes,
            'phone_number' => $this->phoneNumber,
            'wallet_id' => $this->walletId,
            'pin' => $this->pin,
        ], fn($value) => $value !== null);
    }
}

final readonly class Payment
{
    public function __construct(
        public string $id,
        public string $status,
        public string $amount,
        public string $currency,
        public string $paymentMethod,
        public ?string $description = null,
        public ?string $customerId = null,
        public ?string $clientSecret = null,
        public ?string $referenceId = null,
        public ?string $transactionUrl = null,
        public ?string $instructions = null,
        public ?array $metadata = null,
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            amount: $data['amount'],
            currency: $data['currency'],
            paymentMethod: $data['payment_method'],
            description: $data['description'] ?? null,
            customerId: $data['customer_id'] ?? null,
            clientSecret: $data['client_secret'] ?? null,
            referenceId: $data['reference_id'] ?? null,
            transactionUrl: $data['transaction_url'] ?? null,
            instructions: $data['instructions'] ?? null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}

final readonly class CreateWebhookRequest
{
    public function __construct(
        public string $url,
        public array $events,
        public ?string $description = null
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'url' => $this->url,
            'events' => $this->events,
            'description' => $this->description,
        ], fn($value) => $value !== null);
    }
}

final readonly class WebhookEndpoint
{
    public function __construct(
        public string $id,
        public string $url,
        public array $events,
        public string $environment,
        public bool $isActive,
        public string $secret,
        public \DateTimeInterface $createdAt
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            url: $data['url'],
            events: $data['events'],
            environment: $data['environment'],
            isActive: $data['is_active'],
            secret: $data['secret'],
            createdAt: new \DateTimeImmutable($data['created_at'])
        );
    }
}

final readonly class CreateCustomerRequest
{
    public function __construct(
        public string $email,
        public string $name,
        public ?string $phone = null,
        public ?string $description = null,
        public ?array $metadata = null
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'description' => $this->description,
            'metadata' => $this->metadata,
        ], fn($value) => $value !== null);
    }
}

final readonly class Customer
{
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
        public ?string $phone = null,
        public ?string $description = null,
        public ?array $metadata = null,
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            name: $data['name'],
            phone: $data['phone'] ?? null,
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}

final readonly class ApiResponse
{
    public function __construct(
        public bool $success,
        public mixed $data,
        public ?string $message = null,
        public ?string $error = null
    ) {
    }

    public static function fromArray(array $response): self
    {
        return new self(
            success: $response['success'] ?? true,
            data: $response['data'] ?? $response,
            message: $response['message'] ?? null,
            error: $response['error'] ?? null
        );
    }
}