<?php

declare(strict_types=1);

namespace Bunny\Storage;

class Client
{
    private string $apiAccessKey;
    private string $storageZoneName;
    private string $baseUrl;
    private \GuzzleHttp\Client $httpClient;

    public function __construct(
        string $apiKey,
        string $storageZoneName,
        string $storageZoneRegion = Region::FALKENSTEIN,
    ) {
        if (!isset(Region::LIST[$storageZoneRegion])) {
            throw new InvalidRegionException();
        }

        $this->apiAccessKey = $apiKey;
        $this->storageZoneName = $storageZoneName;
        $this->baseUrl = Region::getBaseUrl($storageZoneRegion);

        $this->httpClient = new \GuzzleHttp\Client([
            'allow_redirects' => false,
            'http_errors' => false,
            'base_uri' => $this->baseUrl,
            'headers' => [
                'AccessKey' => $this->apiAccessKey,
            ],
        ]);
    }

    public function listFiles(string $path): mixed
    {
        $response = $this->httpClient->request('GET', $this->normalizePath($path, true));

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
        $response = $this->httpClient->request('DELETE', $this->normalizePath($path));

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
        $response = $this->httpClient->request('PUT', $this->normalizePath($path), $options);

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
        $response = $this->httpClient->request('GET', $this->normalizePath($path));

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
        $response = $this->httpClient->request('DESCRIBE', $this->normalizePath($path));

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

    /**
     * @param string[] $to_delete
     *
     * @return array<string, string>
     */
    public function deleteMultiple(array $to_delete): array
    {
        $requests = [];

        foreach ($to_delete as $path) {
            $requests[$path] = $this->httpClient->requestAsync('DELETE', $this->normalizePath($path));
        }

        $results = \GuzzleHttp\Promise\Utils::unwrap($requests);
        $errors = [];

        /** @var \Psr\Http\Message\ResponseInterface $response */
        foreach ($results as $path => $response) {
            if (200 !== $response->getStatusCode()) {
                $data = json_decode($response->getBody()->getContents(), true);
                if (JSON_ERROR_NONE === json_last_error()) {
                    if (is_array($data) && isset($data['Message'])) {
                        $errors[$path] = $data['Message'];
                        continue;
                    }
                }

                $errors[$path] = $response->getReasonPhrase();
            }
        }

        return $errors;
    }
}
