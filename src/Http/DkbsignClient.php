<?php

namespace Dkbs\LaravelDKBSign\Http;

use Dkbs\LaravelDKBSign\Exceptions\DKBSignException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class DKBSignClient
{
    private ClientInterface $http;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
        private readonly int $timeout = 120,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new Client([
            'base_uri' => rtrim($this->baseUrl, '/').'/',
            'timeout' => $this->timeout,
            'http_errors' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function postJson(string $path, array $body = []): array
    {
        return $this->postJsonResponse($path, $body)->body;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public function postJsonResponse(string $path, array $body = []): ApiResponse
    {
        try {
            $response = $this->http->request('POST', ltrim($path, '/'), [
                'headers' => $this->defaultHeaders(),
                'json' => $body,
            ]);
        } catch (GuzzleException $e) {
            throw new DKBSignException('DKBSign request failed: '.$e->getMessage(), 0, null, $e);
        }

        return $this->toApiResponse($response);
    }

    /**
     * @param  array<string, string>  $fields
     * @param  array<string, array{path: string, filename?: string}>  $files
     * @return array<string, mixed>
     */
    public function postMultipart(string $path, array $fields, array $files = []): array
    {
        return $this->postMultipartResponse($path, $fields, $files)->body;
    }

    /**
     * @param  array<string, string>  $fields
     * @param  array<string, array{path: string, filename?: string}>  $files
     */
    public function postMultipartResponse(string $path, array $fields, array $files = []): ApiResponse
    {
        $parts = [];

        foreach ($files as $name => $file) {
            $parts[] = [
                'name' => $name,
                'path' => $file['path'],
                'filename' => $file['filename'] ?? null,
            ];
        }

        return $this->postMultipartPartsResponse($path, $fields, $parts);
    }

    /**
     * Multipart with repeated field names (e.g. documents[], attachments_0[]).
     *
     * @param  array<string, string>  $fields
     * @param  list<array{name: string, path: string, filename?: string|null}>  $parts
     */
    public function postMultipartPartsResponse(string $path, array $fields, array $parts): ApiResponse
    {
        $multipart = [];

        foreach ($fields as $name => $value) {
            $multipart[] = [
                'name' => $name,
                'contents' => $value,
            ];
        }

        foreach ($parts as $part) {
            $multipart[] = [
                'name' => $part['name'],
                'contents' => fopen($part['path'], 'r'),
                'filename' => $part['filename'] ?? basename($part['path']),
            ];
        }

        try {
            $response = $this->http->request('POST', ltrim($path, '/'), [
                'headers' => $this->defaultHeaders(multipart: true),
                'multipart' => $multipart,
            ]);
        } catch (GuzzleException $e) {
            throw new DKBSignException('DKBSign request failed: '.$e->getMessage(), 0, null, $e);
        }

        return $this->toApiResponse($response);
    }

    /**
     * @throws DKBSignException
     */
    public function downloadBinary(string $url): string
    {
        try {
            $response = $this->http->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->token,
                    'Accept' => 'application/pdf',
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new DKBSignException('DKBSign request failed: '.$e->getMessage(), 0, null, $e);
        }

        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();

        if ($status >= 400) {
            $body = json_decode($raw, true);
            if (! is_array($body)) {
                $body = $raw !== '' ? ['message' => $raw] : [];
            }

            throw DKBSignException::fromResponse(
                $status,
                $body,
                'DKBSign download failed (HTTP '.$status.')',
            );
        }

        return $raw;
    }

    /**
     * @return array<string, string>
     */
    private function defaultHeaders(bool $multipart = false): array
    {
        $headers = [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];

        if (! $multipart) {
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }

    private function toApiResponse(ResponseInterface $response): ApiResponse
    {
        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();
        $body = json_decode($raw, true);

        if (! is_array($body)) {
            $body = $raw !== '' ? ['message' => $raw] : [];
        }

        if ($status >= 400) {
            throw DKBSignException::fromResponse(
                $status,
                $body,
                'DKBSign API error (HTTP '.$status.')',
            );
        }

        return new ApiResponse($status, $body);
    }
}
