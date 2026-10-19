<?php

declare(strict_types=1);

use Dkbs\LaravelDkbsign\Enums\FieldType;
use Dkbs\LaravelDkbsign\Enums\SignatureLevel;
use Dkbs\LaravelDkbsign\Support\Field;
use Dkbs\LaravelDkbsign\Support\Position;

require __DIR__.'/bootstrap.php';

$pdf = __DIR__.'/storage/document.pdf';
if (! is_readable($pdf) || ! str_starts_with((string) @file_get_contents($pdf, false, null, 0, 5), '%PDF-')) {
    passthru(PHP_BINARY.' '.escapeshellarg(__DIR__.'/generate-document.php'), $code);
    if ($code !== 0 || ! is_readable($pdf)) {
        fwrite(STDERR, "PDF invalide ou absent. Lance: php examples/generate-document.php\n");
        exit(1);
    }
}

$otp = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--otp=')) {
        $otp = (int) substr($arg, 6);
    }
}

if ($otp === null) {
    fwrite(STDERR, "Usage: php examples/sign.php --otp=123456\n");
    exit(1);
}

$image = is_readable(__DIR__.'/storage/signature.png')
    ? __DIR__.'/storage/signature.png'
    : __DIR__.'/storage/signature.jpg';

if (! is_readable($pdf)) {
    fwrite(STDERR, "PDF introuvable: {$pdf}\nCopie un PDF de test dans examples/storage/document.pdf\n");
    exit(1);
}

$builder = dkbsign_client()
    ->setFile($pdf)
    ->setSignatureLevel(SignatureLevel::SIMPLE)
    ->addField(0, [
        (new Field(FieldType::SIGNATURE))
            ->usePosition(new Position(x: 120, y: 200, width: 200, height: 70)),
    ]);

if (is_readable($image)) {
    $builder->setSignatureImage($image);
}

$response = $builder->selfSign($otp);

echo json_encode([
    'status' => $response->statusCode,
    'body' => $response->body,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
