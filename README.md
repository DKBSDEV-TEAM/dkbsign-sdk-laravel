# dkbs/dkbsign-sdk-laravel

Package Laravel pour le **self-sign DKBSign** (un signataire : titulaire du token API).

Flux : **OTP** → **signature multipart** du PDF. API inspirée du [SDK PHP DKBSign](https://api.dkbsigns.com).

## Prérequis

- PHP 8.2+
- Laravel 11+ (Illuminate 11+)
- Token API DKBSign avec droit self-sign

## Test manuel dans ce dossier (sans autre app Laravel)

1. Copie `.env.example` → `.env` et mets **`DKBSIGN_TOKEN`** (ta clé API).
2. `composer install`
3. OTP :

```bash
php examples/send-otp.php
```

4. Place un PDF dans `examples/storage/document.pdf`, puis signe avec le code reçu :

```bash
php examples/sign.php --otp=123456
```

Ces scripts utilisent la classe `DKBSign` directement (pas besoin d’`artisan`).

## Installation

### Depuis un dossier local (Laragon)

Dans le `composer.json` de ton application :

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../dkbsign-sdk-laravel",
            "options": { "symlink": false }
        }
    ],
    "require": {
        "dkbs/dkbsign-sdk-laravel": "@dev"
    }
}
```

```bash
composer update dkbs/dkbsign-sdk-laravel
php artisan vendor:publish --tag=dkbsign-config
```

Le Service Provider est découvert via `extra.laravel` dans Composer.

## Configuration

`.env` :

```env
DKBSIGN_BASE_URL=https://api.dkbsigns.com
DKBSIGN_TOKEN=your-api-token
DKBSIGN_TIMEOUT=120
```

## Utilisation (Facade)

```php
use Dkbs\LaravelDKBSign\Facades\DKBSign;
use Dkbs\LaravelDKBSign\Enums\FieldType;
use Dkbs\LaravelDKBSign\Enums\SignatureLevel;
use Dkbs\LaravelDKBSign\Support\Field;
use Dkbs\LaravelDKBSign\Support\Position;

// 1. OTP
$otpResponse = DKBSign::sendOtp();
// $otpResponse->body['email'], expires_in_minutes, etc.

// 2. Signature (nouvelle session fluide à chaque appel)
$response = DKBSign::sign()
    ->setFile(storage_path('app/document.pdf'))
    ->setSignatureImage(storage_path('app/signature.png'))
    ->setSignatureLevel(SignatureLevel::SIMPLE)
    ->addField(0, [
        (new Field(FieldType::SIGNATURE))
            ->usePosition(new Position(x: 120, y: 200, width: 200, height: 70)),
    ])
    ->selfSign(123456);

// $response->statusCode === 200
// $response->body['document_uuid'], signed_pdf_url, verification_url
```

## Utilisation (sans Facade)

```php
use Dkbs\LaravelDKBSign\DKBSign;

$dkbsign = new DKBSign(config('dkbsign.base_url'), config('dkbsign.token'));
```

Ou injection : `DKBSignManager` + `$manager->sign()`.

## Erreurs

Les erreurs API lèvent `Dkbs\LaravelDKBSign\Exceptions\DKBSignException` (`$e->isTimestampUnavailable()` pour HTTP 503 qualified).

## Tests du package

```bash
composer install
composer test
```

Tests avec **Guzzle mock** (pas d’appel réseau réel).

## Périmètre

Inclus : `POST /api/v4/sign/otp`, `POST /api/v4/sign`.

Non inclus : enveloppes multi-signataires, signature publique, vault, workflows.
