<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Bunny\Storage\Client;
use Bunny\Storage\Region;

$apiKey = getenv("BUNNY_STORAGE_API_KEY");
$storageZone = getenv("BUNNY_STORAGE_ZONE");
$region = getenv("BUNNY_STORAGE_REGION") ?: "de";
$scanPath = getenv("BUNNY_SCAN_PATH") ?: "/";
$maxAgeDays = (int) (getenv("BUNNY_MAX_AGE_DAYS") ?: 30);
$dryRun = getenv("BUNNY_DRY_RUN") !== "false";

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

    $cutoffDate = new DateTimeImmutable("-{$maxAgeDays} days");

    $files = $client->listFiles($scanPath);

    if (empty($files)) {
        header("Content-Type: application/json");
        echo json_encode(
            [
                "storageZone" => $storageZone,
                "region" => $region,
                "scanPath" => $scanPath,
                "maxAgeDays" => $maxAgeDays,
                "dryRun" => $dryRun,
                "message" => "No files found in '{$scanPath}'",
            ],
            JSON_PRETTY_PRINT,
        );
        exit(0);
    }

    $oldFiles = [];
    $totalFiles = 0;
    $totalSize = 0;
    $oldSize = 0;

    foreach ($files as $file) {
        if ($file->isDirectory()) {
            continue;
        }

        $totalFiles++;
        $totalSize += $file->getSize();

        if ($file->getDateModified() < $cutoffDate) {
            $oldFiles[] = $file;
            $oldSize += $file->getSize();
        }
    }

    $filesToDelete = array_map(
        fn($file) => [
            "name" => $file->getName(),
            "size" => $file->getSize(),
            "dateModified" => $file->getDateModified()->format("c"),
            "ageDays" => $file->getDateModified()->diff(new DateTimeImmutable())
                ->days,
        ],
        $oldFiles,
    );

    $response = [
        "storageZone" => $storageZone,
        "region" => $region,
        "scanPath" => $scanPath,
        "maxAgeDays" => $maxAgeDays,
        "cutoffDate" => $cutoffDate->format("c"),
        "dryRun" => $dryRun,
        "summary" => [
            "totalFiles" => $totalFiles,
            "totalSize" => $totalSize,
            "oldFilesCount" => count($oldFiles),
            "oldFilesSize" => $oldSize,
        ],
        "filesToDelete" => $filesToDelete,
    ];

    if (!$dryRun && !empty($oldFiles)) {
        $fileNames = array_map(fn($file) => $file->getName(), $oldFiles);
        $errors = $client->deleteMultiple($fileNames);

        $deleted = count($oldFiles) - count($errors);

        $response["deletionResult"] = [
            "deleted" => $deleted,
            "failed" => count($errors),
            "spaceFreed" => $oldSize,
            "errors" => $errors,
        ];
    }

    header("Content-Type: application/json");
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    header("Content-Type: application/json");
    echo json_encode(["error" => $e->getMessage()], JSON_PRETTY_PRINT);
    exit(1);
}
