<?php

namespace Dkbs\LaravelDKBSign;

use Dkbs\LaravelDKBSign\Http\ApiResponse;
use Dkbs\LaravelDKBSign\Http\DKBSignClient;

class DKBSignManager
{
    public function __construct(
        private readonly DKBSignClient $client,
        private readonly string $baseUrl,
        private readonly string $token,
        private readonly int $timeout,
    ) {}

    public function sendOtp(): ApiResponse
    {
        return new ApiResponse(200, $this->client->postJson('api/v4/sign/otp'));
    }

    /**
     * New fluent self-sign session (fresh state for setFile / addField / selfSign).
     */
    public function sign(): DKBSign
    {
        return new DKBSign($this->baseUrl, $this->token, $this->timeout, $this->client);
    }
}
