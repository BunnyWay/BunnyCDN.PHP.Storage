<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Bunny\Storage\Client;
use Bunny\Storage\Region;

$apiKey = getenv('BUNNY_STORAGE_API_KEY');
$storageZone = getenv('BUNNY_STORAGE_ZONE');
$region = getenv('BUNNY_STORAGE_REGION') ?: 'de';

if (!$apiKey || !$storageZone) {
    exit(
        'Error: BUNNY_STORAGE_API_KEY and BUNNY_STORAGE_ZONE environment variables are required.'
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

$message = '';
$success = false;

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    if (UPLOAD_ERR_OK === $file['error']) {
        $tmpPath = $file['tmp_name'];
        $fileName = basename($file['name']);
        $remotePath = "uploads/{$fileName}";

        try {
            $client->upload($tmpPath, $remotePath);
            $message = "File '{$fileName}' uploaded successfully!";
            $success = true;
        } catch (Exception $e) {
            $message = "Upload failed: {$e->getMessage()}";
        }
    } else {
        $message = 'Upload error occurred.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bunny Storage - Form Upload</title>
</head>
<body>
    <h1>Upload a File</h1>

    <?php if ($message) { ?>
        <p style="color: <?php echo $success
            ? 'green'
            : 'red'; ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
