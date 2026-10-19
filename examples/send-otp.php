<?php

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

$response = dkbsign_client()->sendOtp();

echo json_encode([
    'status' => $response->statusCode,
    'body' => $response->body,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
