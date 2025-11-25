<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Bunny\Storage\Client;
use Bunny\Storage\Region;

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

$remotePath = $argv[1] ?? ($_GET['path'] ?? null);

if (!$remotePath) {
    header('Content-Type: application/json');
    echo json_encode(
        [
            'error' => 'No file specified. Pass a remote path as an argument or use ?path= query parameter.',
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

try {
    $client = new Client($apiKey, $storageZone, $regionConstant);

    $client->delete($remotePath);

    header('Content-Type: application/json');
    echo json_encode(
        [
            'storageZone' => $storageZone,
            'region' => $region,
            'path' => $remotePath,
            'status' => 'deleted',
        ],
        JSON_PRETTY_PRINT,
    );
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
    exit(1);
}
