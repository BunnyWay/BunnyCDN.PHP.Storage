<?php

namespace BunnyCDN\Storage;

use BunnyCDN\Storage\Exceptions\BunnyCDNStorageAuthenticationException;
use BunnyCDN\Storage\Exceptions\BunnyCDNStorageException;
use BunnyCDN\Storage\Exceptions\BunnyCDNStorageFileNotFoundException;

class BunnyCDNStorage
{
    /**
     * @var string The name of the storage zone we are working on
     */
    public $storageZoneName = '';

    /**
     * @var string The API access key used for authentication
     */
    public $apiAccessKey = '';

    /**
     * @var string The region used for the request
     */
    public $storageZoneRegion = 'de';

    /**
     * Initializes a new instance of the BunnyCDNStorage class
     *
     * @param $storageZoneName
     * @param $apiAccessKey
     * @param $storageZoneRegion
     */
    public function __construct($storageZoneName, $apiAccessKey, $storageZoneRegion)
    {
        $this->storageZoneName = $storageZoneName;
        $this->apiAccessKey = $apiAccessKey;
        $this->storageZoneRegion = $storageZoneRegion;
    }

    /**
     * Returns the base URL with the endpoint based on the current storage zone region
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storageZoneRegion === "de" || !$this->storageZoneRegion
            ? 'https://storage.bunnycdn.com/'
            : "https://" . $this->storageZoneRegion . ".storage.bunnycdn.com/";
    }

    /**
     * Get the list of storage objects on the given path
     *
     * @param $path
     * @return mixed
     * @throws BunnyCDNStorageException
     */
    public function getStorageObjects($path)
    {
        $normalizedPath = $this->normalizePath($path, true);
        return json_decode($this->sendHttpRequest($normalizedPath));
    }

    /**
     * Delete an object at the given path. If the object is a directory, the contents will also be deleted.
     *
     * @param $path
     * @return bool|string
     * @throws BunnyCDNStorageException
     */
    public function deleteObject($path)
    {
        $normalizedPath = $this->normalizePath($path);
        return $this->sendHttpRequest($normalizedPath, 'DELETE');
    }

    /**
     * Upload a local file to the storage
     *
     * @param $localPath
     * @param $path
     * @return bool|string
     * @throws BunnyCDNStorageException
     */
    public function uploadFile($localPath, $path)
    {
        // Open the local file
        $fileStream = fopen($localPath, 'r');
        if ($fileStream === false) {
            throw new BunnyCDNStorageException('The local file could not be opened.');
        }
        $dataLength = filesize($localPath);
        $normalizedPath = $this->normalizePath($path);
        return $this->sendHttpRequest($normalizedPath, 'PUT', $fileStream, $dataLength);
    }

    /**
     * Download the object to a local file
     *
     * @param $path
     * @param $localPath
     * @return bool|string
     * @throws BunnyCDNStorageException
     */
    public function downloadFile($path, $localPath)
    {
        // Open the local file
        $fileStream = fopen($localPath, 'w+');
        if ($fileStream === false) {
            throw new BunnyCDNStorageException('The local file could not be opened for writing.');
        }

        $dataLength = filesize($localPath);
        $normalizedPath = $this->normalizePath($path);
        return $this->sendHttpRequest($normalizedPath, 'GET', NULL, NULL, $fileStream);
    }

    /**
     * Sends a HTTP Request using cURL
     *
     * @param $url
     * @param string $method
     * @param null $uploadFile
     * @param null $uploadFileSize
     * @param null $downloadFileHandler
     * @return bool|string
     * @throws BunnyCDNStorageException
     */
    private function sendHttpRequest($url, $method = 'GET', $uploadFile = NULL, $uploadFileSize = NULL, $downloadFileHandler = NULL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getBaseUrl() . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AccessKey: {$this->apiAccessKey}",
        ));
        if ($method === 'PUT' && $uploadFile != NULL) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_UPLOAD, 1);
            curl_setopt($ch, CURLOPT_INFILE, $uploadFile);
            curl_setopt($ch, CURLOPT_INFILESIZE, $uploadFileSize);
        } else if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($method === 'GET' && $downloadFileHandler != NULL) {
            curl_setopt($ch, CURLOPT_FILE, $downloadFileHandler);
        }

        $output = curl_exec($ch);
        $curlError = curl_errno($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            throw new BunnyCDNStorageException('An unknown error has occurred during the request. Status code: ' . $curlError);
        }

        if ($responseCode === 404) {
            throw new BunnyCDNStorageFileNotFoundException($url);
        } else if ($responseCode === 401) {
            throw new BunnyCDNStorageAuthenticationException($this->storageZoneName, $this->apiAccessKey);
        } else if ($responseCode < 200 || $responseCode > 299) {
            throw new BunnyCDNStorageException('An unknown error has occurred during the request. Status code: ' . $responseCode);
        }

        return $output;
    }

    /**
     * Normalize a path string
     *
     * @param $path
     * @param null $isDirectory
     * @return false|string|string[]
     * @throws BunnyCDNStorageException
     */
    private function normalizePath($path, $isDirectory = NULL)
    {
        if (!$this->startsWith($path, "/{$this->storageZoneName}/") && !$this->startsWith($path, "{$this->storageZoneName}/")) {
            throw new BunnyCDNStorageException("Path validation failed. File path must begin with /{$this->storageZoneName}/");
        }

        $path = str_replace('\\', '/', $path);
        if ($isDirectory !== NULL) {
            if ($isDirectory) {
                if (!$this->endsWith($path, '/')) {
                    $path = $path . '/';
                }
            } else {
                if ($this->endsWith($path, '/') && $path !== '/') {
                    throw new BunnyCDNStorageException('The requested path is invalid.');
                }
            }
        }

        // Remove double slashes
        while (strpos($path, '//') !== false) {
            $path = str_replace('//', '/', $path);
        }

        // Remove the starting slash
        if (strpos($path, '/') === 0) {
            $path = substr($path, 1);
        }

        return $path;
    }

    /**
     * Starts With Helper
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private function startsWith($haystack, $needle)
    {
        return (strpos($haystack, $needle) === 0);
    }

    /**
     * Ends with Helper
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length === 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
