<?php

namespace Dkbs\LaravelDkbsign\Tests\Unit;

use Dkbs\LaravelDkbsign\Exceptions\DkbsignException;
use Dkbs\LaravelDkbsign\Http\DkbsignClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class DkbsignClientTest extends TestCase
{
    public function test_post_json_decodes_successful_response(): void
    {
        $client = $this->clientWithResponses([
            new Response(200, [], json_encode(['message' => 'OTP sent'], JSON_THROW_ON_ERROR)),
        ]);

        $result = $client->postJson('api/v4/sign/otp');

        $this->assertSame('OTP sent', $result['message']);
    }

    public function test_post_json_throws_dkbsign_exception_on_http_error(): void
    {
        $client = $this->clientWithResponses([
            new Response(401, [], json_encode(['message' => 'Invalid authentication token'], JSON_THROW_ON_ERROR)),
        ]);

        $this->expectException(DkbsignException::class);
        $this->expectExceptionMessage('Invalid authentication token');

        $client->postJson('api/v4/sign/otp');
    }

    public function test_post_multipart_decodes_successful_response(): void
    {
        $client = $this->clientWithResponses([
            new Response(200, [], json_encode([
                'document_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            ], JSON_THROW_ON_ERROR)),
        ]);

        $result = $client->postMultipart(
            'api/v4/sign',
            ['params' => '{"signature_level":"simple"}'],
            ['file' => ['path' => $this->tempFile('sample.pdf', '%PDF-1.4')]],
        );

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $result['document_uuid']);
    }

    public function test_post_json_response_preserves_status_code(): void
    {
        $client = $this->clientWithResponses([
            new Response(202, [], json_encode(['status' => 'success'], JSON_THROW_ON_ERROR)),
        ]);

        $response = $client->postJsonResponse('api/notifications/email', [
            'email' => 'jane@example.com',
            'subject' => 'Test',
            'body' => '<p>Hi</p>',
        ]);

        $this->assertSame(202, $response->statusCode);
        $this->assertSame('success', $response->body['status']);
    }

    public function test_post_multipart_parts_response_preserves_201(): void
    {
        $pdf = $this->tempFile('nda.pdf', '%PDF-1.4');

        $client = $this->clientWithResponses([
            new Response(201, [], json_encode([
                'batch_id' => 'batch-123',
                'signers' => [['email' => 'jane@example.com', 'signing_url' => 'https://sign.test/x']],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $response = $client->postMultipartPartsResponse(
            'api/v4/envelopes',
            ['payload' => '{"title":"NDA"}'],
            [
                ['name' => 'documents[]', 'path' => $pdf],
            ],
        );

        $this->assertSame(201, $response->statusCode);
        $this->assertSame('batch-123', $response->body['batch_id']);
    }

    /**
     * @param  list<Response>  $responses
     */
    private function clientWithResponses(array $responses): DkbsignClient
    {
        $http = new Client([
            'handler' => HandlerStack::create(new MockHandler($responses)),
            'http_errors' => false,
        ]);

        return new DkbsignClient('https://api.dkbsign.test', 'test-token', 30, $http);
    }

    private function tempFile(string $name, string $contents): string
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('dkbsign_', true).'_'.$name;
        file_put_contents($path, $contents);

        return $path;
    }
}
