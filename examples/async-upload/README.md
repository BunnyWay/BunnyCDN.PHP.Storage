# Async Upload Example

This example demonstrates how to upload multiple files concurrently using async/await with Guzzle promises.

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
# Add some files to upload
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
2. Creates async upload promises for each file
3. Executes all uploads concurrently
4. Reports progress and results for each file
5. Compares execution time with sequential uploads

## Benefits of Async Uploads

- **Faster**: Multiple files upload simultaneously instead of one at a time
- **Efficient**: Better utilization of network bandwidth
- **Scalable**: Handle large numbers of files more effectively
