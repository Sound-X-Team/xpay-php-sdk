<?php

declare(strict_types=1);

namespace XPay\Types;

final class ApiResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly array $data,
        public readonly ?string $message = null,
        public readonly bool $success = true
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            statusCode: $data['status_code'] ?? 200,
            data: $data['data'] ?? $data,
            message: $data['message'] ?? null,
            success: $data['success'] ?? true
        );
    }

    public function toArray(): array
    {
        return [
            'status_code' => $this->statusCode,
            'data' => $this->data,
            'message' => $this->message,
            'success' => $this->success,
        ];
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
