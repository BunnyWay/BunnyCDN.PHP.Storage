# Batch Upload Example

This example demonstrates how to upload multiple files from a local directory to Bunny Storage.

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

3. Create a local `files` directory with files to upload:

```bash
mkdir files
echo "File 1 content" > files/file1.txt
echo "File 2 content" > files/file2.txt
echo "File 3 content" > files/file3.txt
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

1. Scans the local `files/` directory for files
2. Uploads each file to Bunny Storage in a `batch/` directory
3. Reports progress and results for each file
4. Shows a summary of successful and failed uploads
