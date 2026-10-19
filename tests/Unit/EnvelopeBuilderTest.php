<?php

namespace Dkbs\LaravelDKBSign\Tests\Unit;

use Dkbs\LaravelDKBSign\EnvelopeBuilder;
use Dkbs\LaravelDKBSign\Enums\FieldType;
use Dkbs\LaravelDKBSign\Enums\SignatureLevel;
use Dkbs\LaravelDKBSign\Enums\SignatureOrder;
use Dkbs\LaravelDKBSign\Exceptions\DKBSignException;
use Dkbs\LaravelDKBSign\Http\DKBSignClient;
use Dkbs\LaravelDKBSign\Support\EnvelopePosition;
use Dkbs\LaravelDKBSign\Support\Signer;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EnvelopeBuilderTest extends TestCase
{
    public function test_envelopes_returns_201_with_batch_id(): void
    {
        $nda = $this->tempFile('nda.pdf', '%PDF-1.4');
        $annex = $this->tempFile('annex.pdf', '%PDF-1.4');
        $attachment = $this->tempFile('id.pdf', '%PDF-1.4');

        $builder = $this->builderWithResponses([
            new Response(201, [], json_encode([
                'batch_id' => 'batch-abc',
                'signers' => [
                    [
                        'email' => 'jane@example.com',
                        'signing_url' => 'https://app.test/sign/uuid-1',
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $response = $builder
            ->setDocuments([$nda, $annex])
            ->setAttachments(0, [$attachment])
            ->setEnvelopeTitle('NDA')
            ->setEnvelopeDescription('Please sign this NDA')
            ->setSignatureOrder(SignatureOrder::ORDERED)
            ->setSignatureLevel(SignatureLevel::ADVANCED)
            ->setSigners([
                new Signer(
                    firstName: 'Jane',
                    lastName: 'Doe',
                    email: 'jane@example.com',
                    phone: '+2250700000001',
                    priority: 1,
                    positions: [
                        new EnvelopePosition(
                            x: 120,
                            y: 200,
                            width: 200,
                            height: 70,
                            page: 0,
                            fieldType: FieldType::SIGNATURE,
                            documentIndex: 0,
                        ),
                    ],
                ),
            ])
            ->envelopes();

        $this->assertSame(201, $response->statusCode);
        $this->assertSame('batch-abc', $response->body['batch_id']);
        $this->assertSame('jane@example.com', $response->body['signers'][0]['email']);
    }

    public function test_envelopes_throws_when_no_documents(): void
    {
        $builder = $this->builderWithResponses([]);

        $this->expectException(DKBSignException::class);
        $this->expectExceptionMessage('At least one document');

        $builder
            ->setSigners([
                new Signer('Jane', 'Doe', 'jane@example.com', '+2250700000001', 1),
            ])
            ->envelopes();
    }

    /**
     * @param  list<Response>  $responses
     */
    private function builderWithResponses(array $responses): EnvelopeBuilder
    {
        $http = new Client([
            'handler' => HandlerStack::create(new MockHandler($responses)),
            'http_errors' => false,
        ]);

        $client = new DKBSignClient('https://api.dkbsign.test', 'token', 30, $http);

        return new EnvelopeBuilder($client);
    }

    private function tempFile(string $name, string $contents): string
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('dkbsign_', true).'_'.$name;
        file_put_contents($path, $contents);

        return $path;
    }
}
