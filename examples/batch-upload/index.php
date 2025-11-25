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

$localDirectory = __DIR__ . "/files";

if (!is_dir($localDirectory)) {
    mkdir($localDirectory, 0755, true);

    file_put_contents(
        "{$localDirectory}/sample1.txt",
        "This is sample file 1. Created at: " . date("Y-m-d H:i:s"),
    );
    file_put_contents(
        "{$localDirectory}/sample2.txt",
        "This is sample file 2. Created at: " . date("Y-m-d H:i:s"),
    );
    file_put_contents(
        "{$localDirectory}/sample3.txt",
        "This is sample file 3. Created at: " . date("Y-m-d H:i:s"),
    );
}

try {
    $client = new Client($apiKey, $storageZone, $regionConstant);

    $files = glob("{$localDirectory}/*");
    $files = array_filter($files, "is_file");

    if (empty($files)) {
        header("Content-Type: application/json");
        echo json_encode(
            [
                "error" => "No files found in '{$localDirectory}'. Add some files to the 'files/' directory and run again.",
            ],
            JSON_PRETTY_PRINT,
        );
        exit(0);
    }

    $results = [];
    $successful = 0;
    $failed = 0;

    foreach ($files as $file) {
        $fileName = basename($file);
        $remotePath = "batch/{$fileName}";

        try {
            $client->upload($file, $remotePath);
            $results[] = [
                "file" => $fileName,
                "size" => filesize($file),
                "status" => "success",
            ];
            $successful++;
        } catch (Exception $e) {
            $results[] = [
                "file" => $fileName,
                "size" => filesize($file),
                "status" => "failed",
                "error" => $e->getMessage(),
            ];
            $failed++;
        }
    }

    $uploadedFiles = $client->listFiles("batch/");
    $uploadedList = array_map(
        fn($file) => [
            "name" => $file->getName(),
            "size" => $file->getSize(),
            "isDirectory" => $file->isDirectory(),
            "dateModified" => $file->getDateModified()->format("c"),
        ],
        array_filter($uploadedFiles, fn($file) => !$file->isDirectory()),
    );

    header("Content-Type: application/json");
    echo json_encode(
        [
            "storageZone" => $storageZone,
            "region" => $region,
            "summary" => [
                "successful" => $successful,
                "failed" => $failed,
                "total" => count($files),
            ],
            "results" => $results,
            "uploadedFiles" => array_values($uploadedList),
        ],
        JSON_PRETTY_PRINT,
    );
} catch (Exception $e) {
    header("Content-Type: application/json");
    echo json_encode(["error" => $e->getMessage()], JSON_PRETTY_PRINT);
    exit(1);
}
