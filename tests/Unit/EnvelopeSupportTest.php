<?php

namespace Dkbs\LaravelDkbsign\Tests\Unit;

use Dkbs\LaravelDkbsign\Enums\FieldType;
use Dkbs\LaravelDkbsign\Enums\SignatureOrder;
use Dkbs\LaravelDkbsign\Support\EmailTemplate;
use Dkbs\LaravelDkbsign\Support\EnvelopePosition;
use Dkbs\LaravelDkbsign\Support\Signer;
use PHPUnit\Framework\TestCase;

class EnvelopeSupportTest extends TestCase
{
    public function test_signature_order_values(): void
    {
        $this->assertSame('ordered', SignatureOrder::ORDERED->value);
        $this->assertSame('any', SignatureOrder::ANY->value);
    }

    public function test_signer_groups_positions_by_document_index(): void
    {
        $signer = new Signer(
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
                new EnvelopePosition(
                    x: 50,
                    y: 100,
                    width: 150,
                    height: 50,
                    page: 1,
                    fieldType: FieldType::DATE,
                    documentIndex: 1,
                ),
            ],
        );

        $payload = $signer->toPayloadArray();

        $this->assertSame('Jane', $payload['first_name']);
        $this->assertSame('Doe', $payload['name']);
        $this->assertSame('jane@example.com', $payload['email']);
        $this->assertSame([
            '0' => [[
                'page' => 0,
                'x' => 120.0,
                'y' => 200.0,
                'width' => 200.0,
                'height' => 70.0,
                'type' => 'signature',
            ]],
            '1' => [[
                'page' => 1,
                'x' => 50.0,
                'y' => 100.0,
                'width' => 150.0,
                'height' => 50.0,
                'type' => 'date',
            ]],
        ], $payload['positions']);
    }

    public function test_email_template_includes_signing_url(): void
    {
        $body = (new EmailTemplate('https://app.example/sign/abc'))->toBody();

        $this->assertStringContainsString('https://app.example/sign/abc', $body);
        $this->assertStringContainsString('<a href=', $body);
    }
}
