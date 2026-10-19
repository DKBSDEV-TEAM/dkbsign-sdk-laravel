<?php

namespace Dkbs\LaravelDKBSign\Tests\Unit;

use Dkbs\LaravelDKBSign\DKBSignManager;
use Dkbs\LaravelDKBSign\Http\DKBSignClient;
use Dkbs\LaravelDKBSign\Support\EmailTemplate;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class SendInvitationTest extends TestCase
{
    public function test_send_invitation_returns_202(): void
    {
        $manager = $this->managerWithResponses([
            new Response(202, [], json_encode([
                'status' => 'success',
                'message' => 'Notification sent successfuly',
            ], JSON_THROW_ON_ERROR)),
        ]);

        $response = $manager->sendInvitation(
            recipientEmail: 'jane@example.com',
            subject: 'Document awaiting signature',
            emailTemplate: new EmailTemplate('https://app.test/sign/uuid-1'),
        );

        $this->assertSame(202, $response->statusCode);
        $this->assertSame('success', $response->body['status']);
    }

    /**
     * @param  list<Response>  $responses
     */
    private function managerWithResponses(array $responses): DKBSignManager
    {
        $http = new Client([
            'handler' => HandlerStack::create(new MockHandler($responses)),
            'http_errors' => false,
        ]);

        $client = new DKBSignClient('https://api.dkbsign.test', 'token', 30, $http);

        return new DKBSignManager($client, 'https://api.dkbsign.test', 'token', 30);
    }
}
