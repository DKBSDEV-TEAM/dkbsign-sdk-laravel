<?php

namespace Dkbs\LaravelDkbsign;

use Dkbs\LaravelDkbsign\Http\ApiResponse;
use Dkbs\LaravelDkbsign\Http\DkbsignClient;

class DkbsignManager
{
    public function __construct(
        private readonly DkbsignClient $client,
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
    public function sign(): Dkbsign
    {
        return new Dkbsign($this->baseUrl, $this->token, $this->timeout, $this->client);
    }
}
