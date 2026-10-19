<?php

namespace Dkbs\LaravelDKBSign;

use Dkbs\LaravelDKBSign\Enums\SignatureLevel;
use Dkbs\LaravelDKBSign\Enums\SignatureOrder;
use Dkbs\LaravelDKBSign\Exceptions\DKBSignException;
use Dkbs\LaravelDKBSign\Http\ApiResponse;
use Dkbs\LaravelDKBSign\Http\DKBSignClient;
use Dkbs\LaravelDKBSign\Support\Signer;
use InvalidArgumentException;
use JsonException;

class EnvelopeBuilder
{
    /**
     * @var list<string>
     */
    private array $documents = [];

    /**
     * @var array<int, list<string>>
     */
    private array $attachmentsByDocumentIndex = [];

    private ?string $envelopeTitle = null;

    private ?string $envelopeDescription = null;

    private SignatureOrder|string|null $signatureOrder = null;

    private SignatureLevel|string|null $signatureLevel = null;

    /**
     * @var list<Signer>
     */
    private array $signers = [];

    public function __construct(
        private readonly DKBSignClient $client,
    ) {}

    /**
     * @param  list<string>  $paths  PDF paths to sign
     */
    public function setDocuments(array $paths): self
    {
        $this->documents = array_values($paths);

        return $this;
    }

    /**
     * @param  list<string>  $paths  Attachment paths for the given document index
     */
    public function setAttachments(int $documentIndex, array $paths): self
    {
        $this->attachmentsByDocumentIndex[$documentIndex] = array_values($paths);

        return $this;
    }

    public function setEnvelopeTitle(string $title): self
    {
        $this->envelopeTitle = $title;

        return $this;
    }

    public function setEnvelopeDescription(string $description): self
    {
        $this->envelopeDescription = $description;

        return $this;
    }

    public function setSignatureOrder(SignatureOrder|string $order): self
    {
        $this->signatureOrder = $order;

        return $this;
    }

    public function setSignatureLevel(SignatureLevel|string $level): self
    {
        $this->signatureLevel = $level;

        return $this;
    }

    /**
     * @param  list<Signer>  $signers
     */
    public function setSigners(array $signers): self
    {
        foreach ($signers as $signer) {
            if (! $signer instanceof Signer) {
                throw new InvalidArgumentException('Each item in $signers must be an instance of '.Signer::class);
            }
        }

        $this->signers = array_values($signers);

        return $this;
    }

    /**
     * @throws DKBSignException
     * @throws JsonException
     */
    public function envelopes(): ApiResponse
    {
        if ($this->documents === []) {
            throw new DKBSignException('At least one document is required. Use setDocuments() before envelopes().');
        }

        if ($this->signers === []) {
            throw new DKBSignException('At least one signer is required. Use setSigners() before envelopes().');
        }

        foreach ($this->documents as $path) {
            if (! is_readable($path)) {
                throw new DKBSignException('Document not readable: '.$path);
            }
        }

        foreach ($this->attachmentsByDocumentIndex as $paths) {
            foreach ($paths as $path) {
                if (! is_readable($path)) {
                    throw new DKBSignException('Attachment not readable: '.$path);
                }
            }
        }

        $payload = $this->buildPayload();

        /** @var list<array{name: string, path: string}> $parts */
        $parts = [];

        foreach ($this->documents as $path) {
            $parts[] = ['name' => 'documents[]', 'path' => $path];
        }

        foreach ($this->attachmentsByDocumentIndex as $index => $paths) {
            $fieldName = 'attachments_'.$index.'[]';
            foreach ($paths as $path) {
                $parts[] = ['name' => $fieldName, 'path' => $path];
            }
        }

        return $this->client->postMultipartPartsResponse(
            'api/v4/envelopes',
            ['payload' => json_encode($payload, JSON_THROW_ON_ERROR)],
            $parts,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(): array
    {
        $payload = [
            'signing_order' => $this->resolveSignatureOrder(),
            'signature_level' => $this->resolveSignatureLevel(),
            'signers' => array_map(
                static fn (Signer $signer): array => $signer->toPayloadArray(),
                $this->signers,
            ),
        ];

        if ($this->envelopeTitle !== null && $this->envelopeTitle !== '') {
            $payload['title'] = $this->envelopeTitle;
        }

        if ($this->envelopeDescription !== null && $this->envelopeDescription !== '') {
            $payload['message'] = $this->envelopeDescription;
        }

        return $payload;
    }

    private function resolveSignatureOrder(): string
    {
        if ($this->signatureOrder instanceof SignatureOrder) {
            return $this->signatureOrder->value;
        }

        if (is_string($this->signatureOrder) && $this->signatureOrder !== '') {
            return $this->signatureOrder;
        }

        return SignatureOrder::ORDERED->value;
    }

    private function resolveSignatureLevel(): string
    {
        if ($this->signatureLevel instanceof SignatureLevel) {
            return $this->signatureLevel->value;
        }

        if (is_string($this->signatureLevel) && $this->signatureLevel !== '') {
            return $this->signatureLevel;
        }

        return SignatureLevel::ADVANCED->value;
    }
}
