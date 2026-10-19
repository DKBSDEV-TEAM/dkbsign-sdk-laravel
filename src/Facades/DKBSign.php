<?php

namespace Dkbs\LaravelDKBSign\Facades;

use Dkbs\LaravelDKBSign\DKBSignManager;
use Dkbs\LaravelDKBSign\Http\ApiResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ApiResponse sendOtp()
 * @method static \Dkbs\LaravelDKBSign\DKBSign sign()
 * @method static \Dkbs\LaravelDKBSign\EnvelopeBuilder envelope()
 * @method static ApiResponse sendInvitation(string $recipientEmail, string $subject, \Dkbs\LaravelDKBSign\Support\EmailTemplate $emailTemplate)
 *
 * @see DKBSignManager
 */
class DKBSign extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DKBSignManager::class;
    }
}
