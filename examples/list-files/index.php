<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Bunny\Storage\Client;
use Bunny\Storage\Region;

$apiKey = getenv('BUNNY_STORAGE_API_KEY');
$storageZone = getenv('BUNNY_STORAGE_ZONE');
$region = getenv('BUNNY_STORAGE_REGION') ?: 'de';

if (!$apiKey || !$storageZone) {
    exit(
        "Error: BUNNY_STORAGE_API_KEY and BUNNY_STORAGE_ZONE environment variables are required.\n"
    );
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

$client = new Client($apiKey, $storageZone, $regionConstant);

$directory = $_GET['directory'] ?? '/';
$files = $client->listFiles($directory);

$output = array_map(
    fn ($file) => [
        'name' => $file->getName(),
        'size' => $file->getSize(),
        'isDirectory' => $file->isDirectory(),
        'dateModified' => $file->getDateModified()->format('c'),
    ],
    $files,
);

header('Content-Type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT);
