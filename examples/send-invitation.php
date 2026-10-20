<?php

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Dkbs\LaravelDKBSign\Support\EmailTemplate;

$options = getopt('', ['email:', 'url:', 'subject:', 'from-last-envelope']);

$email = $options['email'] ?? null;
$url = $options['url'] ?? null;
$subject = $options['subject'] ?? 'Document awaiting signature';

if (isset($options['from-last-envelope'])) {
    $lastFile = __DIR__.'/storage/last-envelope.json';
    if (! is_readable($lastFile)) {
        fwrite(STDERR, "Fichier introuvable: {$lastFile}. Lance d'abord: php examples/create-envelope.php\n");
        exit(1);
    }
    $data = json_decode((string) file_get_contents($lastFile), true);
    $signer = $data['body']['signers'][0] ?? null;
    if (! is_array($signer)) {
        fwrite(STDERR, "Réponse enveloppe invalide dans {$lastFile}\n");
        exit(1);
    }
    $email = $email ?? ($signer['email'] ?? null);
    $url = $url ?? ($signer['signing_url'] ?? null);
}

if ($email === null || $email === '' || $url === null || $url === '') {
    fwrite(STDERR, "Usage:\n");
    fwrite(STDERR, "  php examples/send-invitation.php --email=jane@example.com --url=https://...\n");
    fwrite(STDERR, "  php examples/send-invitation.php --from-last-envelope\n");
    exit(1);
}

$response = dkbsign_manager()->sendInvitation(
    recipientEmail: $email,
    subject: $subject,
    emailTemplate: new EmailTemplate($url),
);

echo json_encode([
    'status' => $response->statusCode,
    'body' => $response->body,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
