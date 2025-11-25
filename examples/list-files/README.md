# List Files Example

This example demonstrates how to list files and directories in Bunny Storage.

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

## Available Regions

| Code  | Location                |
| ----- | ----------------------- |
| `de`  | Falkenstein (default)   |
| `uk`  | London                  |
| `se`  | Stockholm               |
| `ny`  | New York                |
| `la`  | Los Angeles             |
| `sg`  | Singapore               |
| `syd` | Sydney                  |
| `br`  | Sao Paulo               |
| `jh`  | Johannesburg            |

## Running the Example

Run directly with PHP:

```bash
BUNNY_STORAGE_API_KEY="your-key" BUNNY_STORAGE_ZONE="your-zone" php index.php
```

Or start a local server:

```bash
BUNNY_STORAGE_API_KEY="your-key" BUNNY_STORAGE_ZONE="your-zone" composer start
```

Then visit http://localhost:8000 in your browser.

## What This Example Does

1. Connects to your storage zone
2. Lists all files and directories in the root
3. Displays file metadata (name, size, checksum, dates)
4. Shows how to navigate subdirectories
