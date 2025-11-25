# Delete Multiple Files Example

This example demonstrates how to delete multiple files at once from Bunny Storage using `deleteMultiple()`.

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

Pass file paths as arguments:

```bash
php index.php file1.txt file2.txt uploads/file3.txt
```

### Web Server

Start a local server:

```bash
composer start
```

Then visit: http://localhost:8000?paths=file1.txt,file2.txt,uploads/file3.txt

## Output

Returns a JSON response with deletion results:

```json
{
    "storageZone": "your-zone",
    "region": "de",
    "paths": ["file1.txt", "file2.txt", "uploads/file3.txt"],
    "summary": {
        "total": 3,
        "deleted": 2,
        "failed": 1
    },
    "errors": {
        "uploads/file3.txt": "File not found"
    }
}
```
