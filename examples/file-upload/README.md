# File Upload Example

This example demonstrates how to upload a single file from your local filesystem to Bunny Storage.

## Prerequisites

- PHP 8.1 or higher
- Composer
- A Bunny Storage zone with API credentials

## Setup

1. Install dependencies:

```bash
composer install
```

2. Set the required environment variables:

```bash
export BUNNY_STORAGE_API_KEY="your-storage-api-key"
export BUNNY_STORAGE_ZONE="your-storage-zone-name"
export BUNNY_STORAGE_REGION="de"  # Optional, defaults to "de" (Falkenstein)
```

## Running the Example

### CLI

Upload a file (saves to `uploads/` directory by default):

```bash
php index.php /path/to/local/file.txt
```

Specify a custom remote path:

```bash
php index.php /path/to/local/file.txt custom/remote/path.txt
```

### Web Server

Start a local server:

```bash
composer start
```

Then visit: http://localhost:8000?file=/path/to/local/file.txt

Or with a custom remote path: http://localhost:8000?file=/path/to/local/file.txt&remotePath=custom/path.txt

## Output

Returns a JSON response with upload details:

```json
{
  "storageZone": "your-zone",
  "region": "de",
  "file": {
    "localPath": "/path/to/local/file.txt",
    "remotePath": "uploads/file.txt",
    "size": 1234
  },
  "status": "success"
}
```
