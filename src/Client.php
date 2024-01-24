<?php

declare(strict_types=1);

namespace Bunny\Storage;

class Client
{
    private const DEFAULT_STORAGE_ZONE = 'de';

    private string $apiAccessKey;
    private string $storageZoneName;
    private string $storageZoneRegion;
    private string $baseUrl;
    private \GuzzleHttp\Client $httpClient;

    public function __construct(
        string $apiKey,
        string $storageZoneName,
        string $storageZoneRegion = self::DEFAULT_STORAGE_ZONE,
    ) {
        $this->apiAccessKey = $apiKey;
        $this->storageZoneRegion = $storageZoneRegion;
        $this->storageZoneName = $storageZoneName;

        if (self::DEFAULT_STORAGE_ZONE === $this->storageZoneRegion || '' === $this->storageZoneRegion) {
            $this->baseUrl = 'https://storage.bunnycdn.com/';
        } else {
            $this->baseUrl = sprintf('https://%s.storage.bunnycdn.com/', $this->storageZoneRegion);
        }

        $this->httpClient = new \GuzzleHttp\Client([
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
    }

    public function listFiles(string $path): mixed
    {
        $response = $this->makeRequest('GET', $this->normalizePath($path, true));

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (200 === $response->getStatusCode()) {
            return json_decode($response->getBody()->getContents(), true);
        }

        throw new Exception('Could not list files');
    }

    public function delete(string $path): mixed
    {
        $response = $this->makeRequest('DELETE', $this->normalizePath($path));

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (200 === $response->getStatusCode()) {
            return $response->getBody()->getContents();
        }

        /** @var bool|array{Message: string}|null $json */
        $json = json_decode($response->getBody()->getContents(), true);
        $message = 'Could not delete file';

        if (isset($json['Message']) && is_array($json) && is_string($json['Message'])) {
            $message = (string) $json['Message'];
        }

        if (404 === $response->getStatusCode()) {
            throw new FileNotFoundException($message);
        }

        throw new Exception($message);
    }

    public function putContents(string $path, string $contents): string
    {
        return $this->makeUploadRequest($path, ['body' => $contents]);
    }

    public function upload(string $localPath, string $path): string
    {
        $fileStream = fopen($localPath, 'r');
        if (false === $fileStream) {
            throw new Exception('The local file could not be opened.');
        }

        return $this->makeUploadRequest($path, ['body' => $fileStream]);
    }

    /**
     * @param array{body: mixed} $options
     */
    private function makeUploadRequest(string $path, array $options): string
    {
        $response = $this->makeRequest('PUT', $this->normalizePath($path), $options);

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (201 === $response->getStatusCode()) {
            return $response->getBody()->getContents();
        }

        throw new Exception('Could not upload file');
    }

    public function getContents(string $path): string
    {
        $response = $this->makeRequest('GET', $this->normalizePath($path));

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (404 === $response->getStatusCode()) {
            throw new FileNotFoundException($path);
        }

        if (200 === $response->getStatusCode()) {
            return $response->getBody()->getContents();
        }

        throw new Exception('Could not download file');
    }

    public function download(string $path, string $localPath): string
    {
        $result = file_put_contents($localPath, $this->getContents($path));

        if (false === $result) {
            throw new Exception('The local file could not be opened for writing.');
        }

        return $localPath;
    }

    public function exists(string $path): bool
    {
        $response = $this->makeRequest('DESCRIBE', $this->normalizePath($path));

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (404 === $response->getStatusCode()) {
            return false;
        }

        if (200 !== $response->getStatusCode()) {
            throw new Exception('Could not verify if the file exists');
        }

        $metadata = json_decode($response->getBody()->getContents(), true);
        if (!is_array($metadata)) {
            return false;
        }

        return isset($metadata['Guid']) && 36 === strlen($metadata['Guid']);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function makeRequest(string $method, string $path, array $options = []): \Psr\Http\Message\ResponseInterface
    {
        $url = $this->baseUrl.$path;

        $options = array_merge([
            'headers' => [
                'AccessKey' => $this->apiAccessKey,
            ],
        ], $options);

        return $this->httpClient->request($method, $url, $options);
    }

    private function normalizePath(string $path, bool $isDirectory = false): string
    {
        if (!str_starts_with($path, "/{$this->storageZoneName}/") && !str_starts_with($path, "{$this->storageZoneName}/")) {
            $path = "{$this->storageZoneName}/".$path;
        }

        $path = str_replace('\\', '/', $path);

        if (!$isDirectory && '/' !== $path && str_ends_with($path, '/')) {
            throw new Exception('The requested path is invalid.');
        }

        // Remove double slashes
        while (str_contains($path, '//')) {
            $path = str_replace('//', '/', $path);
        }

        $path = ltrim($path, '/');

        if ($isDirectory) {
            $path = $path.'/';
        }

        return $path;
    }
}
