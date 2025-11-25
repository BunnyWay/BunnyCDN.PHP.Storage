<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Bunny\Storage\Client;
use Bunny\Storage\Region;

$apiKey = getenv("BUNNY_STORAGE_API_KEY");
$storageZone = getenv("BUNNY_STORAGE_ZONE");
$region = getenv("BUNNY_STORAGE_REGION") ?: "de";

if (!$apiKey || !$storageZone) {
    header("Content-Type: application/json");
    echo json_encode(
        [
            "error" =>
                "BUNNY_STORAGE_API_KEY and BUNNY_STORAGE_ZONE environment variables are required.",
        ],
        JSON_PRETTY_PRINT,
    );
    exit(1);
}

$paths = [];

if (php_sapi_name() === "cli") {
    $paths = array_slice($argv, 1);
} else {
    $pathsParam = $_GET["paths"] ?? "";
    if ($pathsParam) {
        $paths = array_map("trim", explode(",", $pathsParam));
    }
}

if (empty($paths)) {
    header("Content-Type: application/json");
    echo json_encode(
        [
            "error" =>
                "No files specified. Pass remote paths as arguments or use ?paths=file1.txt,file2.txt query parameter.",
        ],
        JSON_PRETTY_PRINT,
    );
    exit(1);
}

$regionMap = [
    "de" => Region::FALKENSTEIN,
    "uk" => Region::LONDON,
    "se" => Region::STOCKHOLM,
    "ny" => Region::NEW_YORK,
    "la" => Region::LOS_ANGELES,
    "sg" => Region::SINGAPORE,
    "syd" => Region::SYDNEY,
    "br" => Region::SAO_PAULO,
    "jh" => Region::JOHANNESBURG,
];

$regionConstant = $regionMap[$region] ?? Region::FALKENSTEIN;

try {
    $client = new Client($apiKey, $storageZone, $regionConstant);

    $errors = $client->deleteMultiple($paths);

    $deleted = count($paths) - count($errors);

    header("Content-Type: application/json");
    echo json_encode(
        [
            "storageZone" => $storageZone,
            "region" => $region,
            "paths" => $paths,
            "summary" => [
                "total" => count($paths),
                "deleted" => $deleted,
                "failed" => count($errors),
            ],
            "errors" => $errors,
        ],
        JSON_PRETTY_PRINT,
    );
} catch (Exception $e) {
    header("Content-Type: application/json");
    echo json_encode(["error" => $e->getMessage()], JSON_PRETTY_PRINT);
    exit(1);
}
