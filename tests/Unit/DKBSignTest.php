<?php

namespace Dkbs\LaravelDKBSign\Tests\Unit;

use Dkbs\LaravelDKBSign\DKBSign;
use Dkbs\LaravelDKBSign\Enums\FieldType;
use Dkbs\LaravelDKBSign\Enums\SignatureLevel;
use Dkbs\LaravelDKBSign\Exceptions\DKBSignException;
use Dkbs\LaravelDKBSign\Http\DKBSignClient;
use Dkbs\LaravelDKBSign\Support\Field;
use Dkbs\LaravelDKBSign\Support\Position;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class DKBSignTest extends TestCase
{
    public function test_send_otp_returns_api_response(): void
    {
        $client = $this->dkbsignWithResponses([
            new Response(200, [], json_encode(['email' => 'user@example.com'], JSON_THROW_ON_ERROR)),
        ]);

        $response = $client->sendOtp();

        $this->assertSame(200, $response->statusCode);
        $this->assertSame('user@example.com', $response->body['email']);
    }

    public function test_self_sign_returns_signed_document_metadata(): void
    {
        $pdf = $this->tempFile('doc.pdf', '%PDF-1.4');

        $client = $this->dkbsignWithResponses([
            new Response(200, [], json_encode([
                'message' => 'Document signé avec succès',
                'document_uuid' => 'uuid-1',
                'signed_pdf_url' => 'https://api.test/signed',
                'verification_url' => 'https://api.test/verify',
            ], JSON_THROW_ON_ERROR)),
        ]);

        $response = $client
            ->setFile($pdf)
            ->setSignatureLevel(SignatureLevel::SIMPLE)
            ->addField(0, [
                (new Field(FieldType::SIGNATURE))
                    ->usePosition(new Position(10, 20, 100, 50)),
            ])
            ->selfSign(123456);

        $this->assertSame(200, $response->statusCode);
        $this->assertSame('uuid-1', $response->body['document_uuid']);
    }

    public function test_self_sign_throws_when_pdf_missing(): void
    {
        $client = $this->dkbsignWithResponses([]);

        $this->expectException(DKBSignException::class);
        $this->expectExceptionMessage('PDF file not readable');

        $client
            ->setFile('/path/does/not/exist.pdf')
            ->addField(0, [
                (new Field(FieldType::SIGNATURE))
                    ->usePosition(new Position(1, 2, 3, 4)),
            ])
            ->selfSign(123456);
    }

    /**
     * @param  list<Response>  $responses
     */
    private function dkbsignWithResponses(array $responses): DKBSign
    {
        $http = new Client([
            'handler' => HandlerStack::create(new MockHandler($responses)),
            'http_errors' => false,
        ]);

        $client = new DKBSignClient('https://api.dkbsign.test', 'token', 30, $http);

        return new DKBSign('https://api.dkbsign.test', 'token', 30, $client);
    }

    private function tempFile(string $name, string $contents): string
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('dkbsign_', true).'_'.$name;
        file_put_contents($path, $contents);

        return $path;
    }
}
