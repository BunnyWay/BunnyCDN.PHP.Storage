# Download File Example

This example demonstrates how to download a file from Bunny Storage to your local filesystem.

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

Download a file (saves to `downloads/` directory by default):

```bash
php index.php remote/path/file.txt
```

Specify a custom local path:

```bash
php index.php remote/path/file.txt /path/to/local/file.txt
```

### Web Server

Start a local server:

```bash
composer start
```

Then visit: http://localhost:8000?path=remote/path/file.txt

Or with a custom local path: http://localhost:8000?path=remote/path/file.txt&localPath=/tmp/file.txt

## Output

Returns a JSON response with download details:

```json
{
    "storageZone": "your-zone",
    "region": "de",
    "file": {
        "remotePath": "remote/path/file.txt",
        "localPath": "/path/to/downloads/file.txt",
        "size": 1234
    },
    "status": "success"
}
```
