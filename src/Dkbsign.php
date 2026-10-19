<?php

namespace Dkbs\LaravelDkbsign;

use Dkbs\LaravelDkbsign\Enums\SignatureLevel;
use Dkbs\LaravelDkbsign\Exceptions\DkbsignException;
use Dkbs\LaravelDkbsign\Http\ApiResponse;
use Dkbs\LaravelDkbsign\Http\DkbsignClient;
use Dkbs\LaravelDkbsign\Support\Field;
use InvalidArgumentException;
use JsonException;

class Dkbsign
{
    private readonly DkbsignClient $client;

    private ?string $filePath = null;

    private ?string $signatureImagePath = null;

    private SignatureLevel|string|null $signatureLevel = null;

    /**
     * @var array<int, list<Field>>
     */
    private array $fieldsByPage = [];

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
        private readonly int $timeout = 120,
        ?DkbsignClient $client = null,
    ) {
        $this->client = $client ?? new DkbsignClient($baseUrl, $token, $timeout);
    }

    public function sendOtp(): ApiResponse
    {
        $body = $this->client->postJson('api/v4/sign/otp');

        return new ApiResponse(200, $body);
    }

    public function setFile(string $path): self
    {
        $this->filePath = $path;

        return $this;
    }

    public function setSignatureImage(string $path): self
    {
        $this->signatureImagePath = $path;

        return $this;
    }

    public function setSignatureLevel(SignatureLevel|string $level): self
    {
        $this->signatureLevel = $level;

        return $this;
    }

    /**
     * @param  list<Field>  $fields
     */
    public function addField(int $page, array $fields): self
    {
        foreach ($fields as $field) {
            if (! $field instanceof Field) {
                throw new InvalidArgumentException('Each item in $fields must be an instance of '.Field::class);
            }
        }

        if (! isset($this->fieldsByPage[$page])) {
            $this->fieldsByPage[$page] = [];
        }

        array_push($this->fieldsByPage[$page], ...$fields);

        return $this;
    }

    /**
     * @throws DkbsignException
     * @throws JsonException
     */
    public function selfSign(int $otpCode, ?string $mfaCode = null, ?string $secondOtpCode = null): ApiResponse
    {
        if ($this->filePath === null || ! is_readable($this->filePath)) {
            throw new DkbsignException('PDF file not readable: '.($this->filePath ?? '(not set)'));
        }

        if ($this->fieldsByPage === []) {
            throw new DkbsignException('At least one field is required. Use addField() before selfSign().');
        }

        if ($this->signatureImagePath !== null && ! is_readable($this->signatureImagePath)) {
            throw new DkbsignException('Signature image not readable: '.$this->signatureImagePath);
        }

        $params = $this->buildParams($otpCode, $mfaCode, $secondOtpCode);

        $fields = [
            'params' => json_encode($params, JSON_THROW_ON_ERROR),
        ];

        $files = [
            'file' => ['path' => $this->filePath],
        ];

        if ($this->signatureImagePath !== null) {
            $files['signature_image'] = ['path' => $this->signatureImagePath];
        }

        $body = $this->client->postMultipart('api/v4/sign', $fields, $files);

        return new ApiResponse(200, $body);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildParams(int $otpCode, ?string $mfaCode, ?string $secondOtpCode): array
    {
        ksort($this->fieldsByPage);

        $pages = [];
        foreach ($this->fieldsByPage as $pageNumber => $fields) {
            $pages[] = [
                'page' => $pageNumber,
                'signatures' => array_map(
                    static fn (Field $field): array => $field->toArray(),
                    $fields,
                ),
            ];
        }

        $params = [
            'signature_level' => $this->resolveSignatureLevel(),
            'pages' => $pages,
            'otp_code' => $otpCode,
        ];

        if ($mfaCode !== null) {
            $params['mfa_code'] = $mfaCode;
        }

        if ($secondOtpCode !== null) {
            $params['second_otp_code'] = $secondOtpCode;
        }

        return $params;
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
