<?php

namespace Dkbs\LaravelDKBSign;

use Dkbs\LaravelDKBSign\Http\ApiResponse;
use Dkbs\LaravelDKBSign\Http\DKBSignClient;
use Dkbs\LaravelDKBSign\Support\EmailTemplate;

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

    /**
     * New fluent envelope session (documents, signers, then envelopes()).
     */
    public function envelope(): EnvelopeBuilder
    {
        return new EnvelopeBuilder($this->client);
    }

    /**
     * Queue an invitation email for a signer (HTTP 202).
     */
    public function sendInvitation(
        string $recipientEmail,
        string $subject,
        EmailTemplate $emailTemplate,
    ): ApiResponse {
        return $this->client->postJsonResponse('api/notifications/email', [
            'email' => $recipientEmail,
            'subject' => $subject,
            'body' => $emailTemplate->toBody(),
        ]);
    }
}
