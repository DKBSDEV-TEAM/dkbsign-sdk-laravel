<?php

declare(strict_types=1);

use Dkbs\LaravelDkbsign\Exceptions\DkbsignException;
use Dkbs\LaravelDkbsign\Http\DkbsignClient;

require __DIR__.'/bootstrap.php';

$url = $argv[1] ?? null;
$output = $argv[2] ?? 'signed-output.pdf';

if ($url === null || $url === '') {
    fwrite(STDERR, "Usage: php examples/download-signed.php \"SIGNED_PDF_URL\" [output.pdf]\n");
    exit(1);
}

$client = new DkbsignClient(
    (string) dkbsign_config('base_url'),
    (string) dkbsign_config('token'),
    (int) dkbsign_config('timeout'),
);

try {
    $bytes = $client->downloadBinary($url);
} catch (DkbsignException $e) {
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    exit(1);
}

file_put_contents($output, $bytes);
echo 'Saved: '.$output.' ('.strlen($bytes)." bytes)\n";
