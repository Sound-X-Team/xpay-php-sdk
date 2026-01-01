<?php

declare(strict_types=1);

namespace XPay\Exceptions;

class XPayException extends \Exception
{
  public function __construct(
    string $message,
    public readonly string $errorCode,
    public readonly ?int $status = null,
    public readonly mixed $details = null,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 0, $previous);
  }

  public function getErrorCode(): string
  {
    return $this->errorCode;
  }

  public function getHttpStatus(): ?int
  {
    return $this->status;
  }

  public function getDetails(): mixed
  {
    return $this->details;
  }
}

final class AuthenticationException extends XPayException
{
  public function __construct(
    string $message = 'Authentication failed',
    mixed $details = null,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 'AUTHENTICATION_ERROR', 401, $details, $previous);
  }
}

final class ValidationException extends XPayException
{
  public function __construct(
    string $message = 'Validation failed',
    mixed $details = null,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 'VALIDATION_ERROR', 400, $details, $previous);
  }
}

final class NetworkException extends XPayException
{
  public function __construct(
    string $message = 'Network error',
    mixed $details = null,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 'NETWORK_ERROR', null, $details, $previous);
  }
}

final class TimeoutException extends XPayException
{
  public function __construct(
    string $message = 'Request timeout',
    mixed $details = null,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 'TIMEOUT', 408, $details, $previous);
  }
}

final class ResourceNotFoundException extends XPayException
{
  public function __construct(
    string $message = 'Resource not found',
    mixed $details = null,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 'NOT_FOUND', 404, $details, $previous);
  }
}

final class PermissionException extends XPayException
{
  public function __construct(
    string $message = 'Permission denied',
    mixed $details = null,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 'PERMISSION_DENIED', 403, $details, $previous);
  }
}

