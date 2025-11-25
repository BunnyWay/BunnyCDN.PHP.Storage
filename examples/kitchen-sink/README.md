# Kitchen Sink Example

A comprehensive example demonstrating multiple Bunny Storage operations in a single web interface: uploading, listing, and deleting files.

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

Start the local server:

```bash
BUNNY_STORAGE_API_KEY="your-key" BUNNY_STORAGE_ZONE="your-zone" composer start
```

Then visit http://localhost:8000 in your browser.

## Features

- **Upload**: HTML form for uploading files to Bunny Storage
- **List**: Displays all uploaded files in a table with name, size, and modified date
- **Delete**: Remove files directly from the interface

## What This Example Demonstrates

1. Handling HTML form file uploads with `$_FILES`
2. Uploading files using `$client->upload()`
3. Listing files using `$client->listFiles()`
4. Deleting files using `$client->delete()`
5. Proper error handling for upload errors
6. Formatting file sizes for display
