<?php

namespace Dkbs\LaravelDKBSign\Http;

readonly class ApiResponse
{
    /**
     * @param  array<string, mixed>  $body
     */
    public function __construct(
        public int $statusCode,
        public array $body,
    ) {}
}
