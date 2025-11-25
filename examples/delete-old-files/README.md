# Delete Old Files Example

This example demonstrates how to find and delete files older than a specified age from Bunny Storage.

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

## Configuration

You can customize the behavior by setting additional environment variables:

```bash
# Directory to scan (default: root)
export BUNNY_SCAN_PATH="/"

# Maximum age in days (default: 30)
export BUNNY_MAX_AGE_DAYS="30"

# Dry run mode - don't actually delete (default: true)
export BUNNY_DRY_RUN="true"
```

## What This Example Does

1. Lists all files in the specified directory
2. Identifies files older than the specified age
3. In dry run mode: shows what would be deleted
4. In live mode: deletes old files using batch deletion
5. Reports results including any errors

## Use Cases

- Cleaning up temporary files
- Removing old log files
- Managing storage costs by removing outdated content
- Automated maintenance scripts (via cron)
