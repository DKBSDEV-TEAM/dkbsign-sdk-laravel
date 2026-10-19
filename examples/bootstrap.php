<?php

declare(strict_types=1);

$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';

$envFile = $root.'/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\"'");
        if ($name !== '' && getenv($name) === false) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}

function dkbsign_config(string $key, mixed $default = null): mixed
{
    return match ($key) {
        'base_url' => getenv('DKBSIGN_BASE_URL') ?: 'https://api.dkbsigns.com',
        'token' => getenv('DKBSIGN_TOKEN') ?: '',
        'timeout' => (int) (getenv('DKBSIGN_TIMEOUT') ?: 120),
        default => $default,
    };
}

function dkbsign_client(): Dkbs\LaravelDkbsign\Dkbsign
{
    $token = (string) dkbsign_config('token');
    if ($token === '') {
        fwrite(STDERR, "DKBSIGN_TOKEN manquant. Copie .env.example vers .env et mets ta clé API.\n");
        exit(1);
    }

    return new Dkbs\LaravelDkbsign\Dkbsign(
        (string) dkbsign_config('base_url'),
        $token,
        (int) dkbsign_config('timeout'),
    );
}
