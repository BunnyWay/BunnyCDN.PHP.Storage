<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Bunny\Storage\Client;
use Bunny\Storage\Region;

$apiKey = getenv("BUNNY_STORAGE_API_KEY");
$storageZone = getenv("BUNNY_STORAGE_ZONE");
$region = getenv("BUNNY_STORAGE_REGION") ?: "de";

if (!$apiKey || !$storageZone) {
    die(
        "Error: BUNNY_STORAGE_API_KEY and BUNNY_STORAGE_ZONE environment variables are required."
    );
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

$client = new Client($apiKey, $storageZone, $regionConstant);

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["file"])) {
    $file = $_FILES["file"];

    if ($file["error"] === UPLOAD_ERR_OK) {
        $tmpPath = $file["tmp_name"];
        $fileName = basename($file["name"]);
        $remotePath = "uploads/{$fileName}";

        try {
            $client->upload($tmpPath, $remotePath);
            $message = "File '{$fileName}' uploaded successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Upload failed: {$e->getMessage()}";
            $messageType = "error";
        }
    } else {
        $errorMessage = getUploadErrorMessage($file["error"]);
        $message = "Upload error: {$errorMessage}";
        $messageType = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete"])) {
    $fileToDelete = $_POST["delete"];
    try {
        $client->delete("uploads/{$fileToDelete}");
        $message = "File '{$fileToDelete}' deleted successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Delete failed: {$e->getMessage()}";
        $messageType = "error";
    }
}

$files = [];
try {
    $files = $client->listFiles("uploads/");
} catch (Exception $e) {
    // Directory might not exist yet
}

function getUploadErrorMessage(int $errorCode): string
{
    return match ($errorCode) {
        UPLOAD_ERR_INI_SIZE => "File exceeds upload_max_filesize directive",
        UPLOAD_ERR_FORM_SIZE => "File exceeds MAX_FILE_SIZE directive",
        UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
        UPLOAD_ERR_NO_FILE => "No file was uploaded",
        UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
        UPLOAD_ERR_EXTENSION => "A PHP extension stopped the upload",
        default => "Unknown upload error",
    };
}

function formatBytes(int $bytes, int $precision = 2): string
{
    $units = ["B", "KB", "MB", "GB", "TB"];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= 1 << 10 * $pow;

    return round($bytes, $precision) . " " . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bunny Storage - File Upload Example</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #333; }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success { background: #d4edda; color: #155724; }
        .message.error { background: #f8d7da; color: #721c24; }
        form { display: flex; gap: 10px; align-items: center; }
        input[type="file"] { flex: 1; }
        button {
            background: #ff6600;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover { background: #e55c00; }
        button.delete {
            background: #dc3545;
            padding: 5px 10px;
            font-size: 12px;
        }
        button.delete:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; }
        .empty { color: #666; font-style: italic; }
    </style>
</head>
<body>
    <h1>Bunny Storage - File Upload</h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars(
    $message,
) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Upload a File</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit">Upload</button>
        </form>
    </div>

    <div class="card">
        <h2>Uploaded Files</h2>
        <?php if (empty($files)): ?>
            <p class="empty">No files uploaded yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                        <?php if (!$file->isDirectory()): ?>
                            <tr>
                                <td><?= htmlspecialchars(
                                    $file->getName(),
                                ) ?></td>
                                <td><?= formatBytes($file->getSize()) ?></td>
                                <td><?= $file
                                    ->getDateModified()
                                    ->format("Y-m-d H:i") ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete" value="<?= htmlspecialchars(
                                            $file->getName(),
                                        ) ?>">
                                        <button type="submit" class="delete" onclick="return confirm('Delete this file?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
