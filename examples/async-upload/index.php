<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Bunny\Storage\Client;
use Bunny\Storage\Region;
use GuzzleHttp\Promise\Utils;

$apiKey = getenv('BUNNY_STORAGE_API_KEY');
$storageZone = getenv('BUNNY_STORAGE_ZONE');
$region = getenv('BUNNY_STORAGE_REGION') ?: 'de';

if (!$apiKey || !$storageZone) {
    header('Content-Type: application/json');
    echo json_encode(
        [
            'error' => 'BUNNY_STORAGE_API_KEY and BUNNY_STORAGE_ZONE environment variables are required.',
        ],
        JSON_PRETTY_PRINT,
    );
    exit(1);
}

$regionMap = [
    'de' => Region::FALKENSTEIN,
    'uk' => Region::LONDON,
    'se' => Region::STOCKHOLM,
    'ny' => Region::NEW_YORK,
    'la' => Region::LOS_ANGELES,
    'sg' => Region::SINGAPORE,
    'syd' => Region::SYDNEY,
    'br' => Region::SAO_PAULO,
    'jh' => Region::JOHANNESBURG,
];

$regionConstant = $regionMap[$region] ?? Region::FALKENSTEIN;

$localDirectory = __DIR__.'/files';

if (!is_dir($localDirectory)) {
    mkdir($localDirectory, 0755, true);

    for ($i = 1; $i <= 5; ++$i) {
        $content = str_repeat("This is sample file {$i}. ", 100 * $i);
        file_put_contents("{$localDirectory}/async-sample{$i}.txt", $content);
    }
}

try {
    $client = new Client($apiKey, $storageZone, $regionConstant);

    $files = glob("{$localDirectory}/*");
    $files = array_filter($files, 'is_file');

    if (empty($files)) {
        header('Content-Type: application/json');
        echo json_encode(
            [
                'error' => "No files found in '{$localDirectory}'. Add some files to the 'files/' directory and run again.",
            ],
            JSON_PRETTY_PRINT,
        );
        exit(0);
    }

    $uploadMap = [];
    foreach ($files as $file) {
        $fileName = basename($file);
        $remotePath = "async/{$fileName}";
        $uploadMap[$file] = $remotePath;
    }

    $asyncStart = microtime(true);

    $promises = [];
    foreach ($uploadMap as $localPath => $remotePath) {
        $promises[$remotePath] = $client->uploadAsync($localPath, $remotePath);
    }

    $promiseResults = Utils::settle($promises)->wait();

    $asyncEnd = microtime(true);
    $asyncDuration = round($asyncEnd - $asyncStart, 3);

    $results = [];
    $successful = 0;
    $failed = 0;

    foreach ($promiseResults as $remotePath => $result) {
        $fileName = basename($remotePath);
        $localPath = array_search($remotePath, $uploadMap);

        if ('fulfilled' === $result['state']) {
            $results[] = [
                'file' => $fileName,
                'size' => filesize($localPath),
                'status' => 'success',
            ];
            ++$successful;
        } else {
            $results[] = [
                'file' => $fileName,
                'size' => filesize($localPath),
                'status' => 'failed',
                'error' => $result['reason']->getMessage(),
            ];
            ++$failed;
        }
    }

    $uploadedFiles = $client->listFiles('async/');
    $uploadedList = array_map(
        fn ($file) => [
            'name' => $file->getName(),
            'size' => $file->getSize(),
            'isDirectory' => $file->isDirectory(),
            'dateModified' => $file->getDateModified()->format('c'),
        ],
        array_filter($uploadedFiles, fn ($file) => !$file->isDirectory()),
    );

    header('Content-Type: application/json');
    echo json_encode(
        [
            'storageZone' => $storageZone,
            'region' => $region,
            'summary' => [
                'successful' => $successful,
                'failed' => $failed,
                'total' => count($files),
                'durationSeconds' => $asyncDuration,
            ],
            'results' => $results,
            'uploadedFiles' => array_values($uploadedList),
        ],
        JSON_PRETTY_PRINT,
    );
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
    exit(1);
}
