# File Info Example

This example demonstrates how to get metadata and details about a file in Bunny Storage.

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

```bash
php index.php remote/path/file.txt
```

### Web Server

Start a local server:

```bash
composer start
```

Then visit: http://localhost:8000?path=remote/path/file.txt

## Output

Returns a JSON response with file metadata:

```json
{
    "storageZone": "your-zone",
    "region": "de",
    "file": {
        "path": "remote/path/file.txt",
        "name": "file.txt",
        "size": 1234,
        "isDirectory": false,
        "dateModified": "2024-01-15T10:30:00+00:00",
        "dateCreated": "2024-01-10T08:00:00+00:00",
        "checksum": "abc123...",
        "contentType": "text/plain"
    }
}
```
