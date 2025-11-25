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

$localFile = $argv[1] ?? ($_GET["file"] ?? null);

if (!$localFile) {
    header("Content-Type: application/json");
    echo json_encode(
        [
            "error" =>
                "No file specified. Pass a file path as an argument or use ?file= query parameter.",
        ],
        JSON_PRETTY_PRINT,
    );
    exit(1);
}

if (!file_exists($localFile)) {
    header("Content-Type: application/json");
    echo json_encode(
        ["error" => "File not found: {$localFile}"],
        JSON_PRETTY_PRINT,
    );
    exit(1);
}

if (!is_file($localFile)) {
    header("Content-Type: application/json");
    echo json_encode(
        ["error" => "Path is not a file: {$localFile}"],
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

    $fileName = basename($localFile);
    $remotePath = $argv[2] ?? ($_GET["remotePath"] ?? "uploads/{$fileName}");

    $client->upload($localFile, $remotePath);

    header("Content-Type: application/json");
    echo json_encode(
        [
            "storageZone" => $storageZone,
            "region" => $region,
            "file" => [
                "localPath" => $localFile,
                "remotePath" => $remotePath,
                "size" => filesize($localFile),
            ],
            "status" => "success",
        ],
        JSON_PRETTY_PRINT,
    );
} catch (Exception $e) {
    header("Content-Type: application/json");
    echo json_encode(["error" => $e->getMessage()], JSON_PRETTY_PRINT);
    exit(1);
}
