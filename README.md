# dkbs/dkbsign-sdk-laravel

Package Laravel pour **DKBSign** : **self-sign** (titulaire du token) et **enveloppes** multi-signataires (création + invitation email).

API inspirée du [SDK PHP DKBSign](https://api.dkbsigns.com).

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

Ces scripts utilisent le SDK directement (pas besoin d’`artisan`).

### Enveloppes (exemples)

1. Créer le parapheur (HTTP **201**, sans envoi d’email automatique) :

```bash
php examples/create-envelope.php
```

2. Inviter le signataire (HTTP **202**, file d’envoi) :

```bash
php examples/send-invitation.php --from-last-envelope
```

Ou avec email et URL explicites :

```bash
php examples/send-invitation.php --email=jane@example.com --url="https://..."
```

Variable optionnelle : `DKBSIGN_TEST_SIGNER_EMAIL` pour l’email du signataire dans `create-envelope.php`.

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

## Enveloppes (Facade)

```php
use Dkbs\LaravelDKBSign\Facades\DKBSign;
use Dkbs\LaravelDKBSign\Enums\FieldType;
use Dkbs\LaravelDKBSign\Enums\SignatureLevel;
use Dkbs\LaravelDKBSign\Enums\SignatureOrder;
use Dkbs\LaravelDKBSign\Support\EmailTemplate;
use Dkbs\LaravelDKBSign\Support\EnvelopePosition;
use Dkbs\LaravelDKBSign\Support\Signer;

// 1. Créer l’enveloppe (201)
$envelope = DKBSign::envelope()
    ->setDocuments([storage_path('app/nda.pdf'), storage_path('app/annex.pdf')])
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
                    x: 120, y: 200, width: 200, height: 70,
                    page: 0, fieldType: FieldType::SIGNATURE, documentIndex: 0,
                ),
            ],
        ),
    ])
    ->envelopes();

// 2. Inviter (202)
$signer = $envelope->body['signers'][0];

$queued = DKBSign::sendInvitation(
    recipientEmail: $signer['email'],
    subject: 'Document awaiting signature',
    emailTemplate: new EmailTemplate($signer['signing_url']),
);
```

Pas d’OTP côté initiateur : les signataires signent via `signing_url` dans le navigateur.

## Erreurs

Les erreurs API lèvent `Dkbs\LaravelDKBSign\Exceptions\DKBSignException` (`$e->isTimestampUnavailable()` pour HTTP 503 qualified).

## Tests du package

```bash
composer install
composer test
```

Tests avec **Guzzle mock** (pas d’appel réseau réel).

## Périmètre

Inclus :

- Self-sign : `POST /api/v4/sign/otp`, `POST /api/v4/sign`
- Enveloppes (initiateur) : `POST /api/v4/envelopes`, `POST /api/notifications/email`

Non inclus : flux public signataire (`/api/v4/public/envelopes/...`), vault, workflows, listes / suivi avancé des parapheurs.
