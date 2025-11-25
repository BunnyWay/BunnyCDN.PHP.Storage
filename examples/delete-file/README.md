# Delete File Example

This example demonstrates how to delete files and directories from Bunny Storage.

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

1. Creates a test file in storage
2. Verifies the file exists
3. Deletes the file
4. Verifies the file was deleted
5. Demonstrates deleting multiple files at once
6. Shows error handling for non-existent files
