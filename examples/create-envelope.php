<?php

declare(strict_types=1);

require __DIR__.'/bootstrap.php';
require __DIR__.'/generate-document.php';

use Dkbs\LaravelDKBSign\Enums\FieldType;
use Dkbs\LaravelDKBSign\Enums\SignatureLevel;
use Dkbs\LaravelDKBSign\Enums\SignatureOrder;
use Dkbs\LaravelDKBSign\Support\EnvelopePosition;
use Dkbs\LaravelDKBSign\Support\Signer;

$storage = __DIR__.'/storage';
if (! is_dir($storage)) {
    mkdir($storage, 0777, true);
}

$ndaPath = $storage.'/nda.pdf';
$annexPath = $storage.'/annex.pdf';

if (! is_readable($ndaPath) || ! str_starts_with((string) file_get_contents($ndaPath, false, null, 0, 5), '%PDF-')) {
    file_put_contents($ndaPath, build_test_pdf(['NDA — document principal']));
}

if (! is_readable($annexPath) || ! str_starts_with((string) file_get_contents($annexPath, false, null, 0, 5), '%PDF-')) {
    file_put_contents($annexPath, build_test_pdf(['Annexe au NDA']));
}

$manager = dkbsign_manager();

$response = $manager->envelope()
    ->setDocuments([$ndaPath, $annexPath])
    ->setEnvelopeTitle('NDA')
    ->setEnvelopeDescription('Please sign this NDA')
    ->setSignatureOrder(SignatureOrder::ORDERED)
    ->setSignatureLevel(SignatureLevel::ADVANCED)
    ->setSigners([
        new Signer(
            firstName: 'Jane',
            lastName: 'Doe',
            email: getenv('DKBSIGN_TEST_SIGNER_EMAIL') ?: 'jane@example.com',
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
                    x: 120,
                    y: 200,
                    width: 200,
                    height: 70,
                    page: 0,
                    fieldType: FieldType::SIGNATURE,
                    documentIndex: 1,
                ),
            ],
        ),
    ])
    ->envelopes();

$out = [
    'status' => $response->statusCode,
    'body' => $response->body,
];

$lastFile = $storage.'/last-envelope.json';
file_put_contents($lastFile, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
echo PHP_EOL."Saved: {$lastFile}".PHP_EOL;
