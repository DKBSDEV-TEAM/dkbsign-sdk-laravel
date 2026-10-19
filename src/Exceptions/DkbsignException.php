<?php

namespace Dkbs\LaravelDkbsign\Exceptions;

use Exception;
use Throwable;

class DkbsignException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly ?array $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public static function fromResponse(int $statusCode, ?array $body, string $fallbackMessage): self
    {
        $message = is_array($body)
            ? (string) ($body['message'] ?? $body['error'] ?? $fallbackMessage)
            : $fallbackMessage;

        return new self($message, $statusCode, $body);
    }

    public function isTimestampUnavailable(): bool
    {
        if ($this->statusCode !== 503) {
            return false;
        }

        $message = $this->responseBody['message'] ?? $this->getMessage();

        return str_contains((string) $message, 'TIMESTAMP_UNAVAILABLE_RETRY');
    }
}
