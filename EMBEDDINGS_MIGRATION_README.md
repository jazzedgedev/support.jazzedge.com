# Embeddings Data Migration Guide

This guide explains how to migrate `wp_alm_transcript_embeddings` table data from a source server to WPEngine using REST API endpoints.

## Overview

The migration system consists of:
1. **REST API Endpoints** - Added to `academy-ai-assistant` plugin for exporting and importing embeddings data
2. **Migration Script** - Standalone PHP script (`migrate-embeddings.php`) that transfers data between servers

## Server Setup

### Local Server (Source - Has the Database)
**What needs to be on your local server:**
- ✅ `chapter-transcription` plugin (Chapter Transcription Manager) with REST API endpoints (for **exporting** data)
- ✅ `wp_alm_transcript_embeddings` table with your data
- ✅ `migrate-embeddings.php` script (runs from here)
- ✅ WordPress Application Password for authentication

**The local server will:**
- Export embeddings data via REST API using `/wp-json/chapter-transcription/v1/embeddings/export`
- Run the migration script that coordinates the transfer

**Note:** The migration script will automatically try the `chapter-transcription` endpoint first, then fall back to `academy-ai-assistant` if needed.

### WPEngine Server (Destination)
**What needs to be on WPEngine:**
- ✅ `academy-ai-assistant` plugin with REST API endpoints (for **importing** data)
- ✅ `wp_alm_transcript_embeddings` table structure (can be empty initially)
- ✅ WordPress Application Password for authentication

**The WPEngine server will:**
- Receive and import embeddings data via REST API

## Prerequisites

### 1. WordPress Application Passwords

You need to create Application Passwords for both source and destination servers:

1. Log into WordPress Admin on each server
2. Go to **Users > Your Profile**
3. Scroll down to **Application Passwords** section
4. Create a new application password (e.g., "Embeddings Migration")
5. **Copy the password immediately** - it won't be shown again!

### 2. REST API Endpoints

The following endpoints are automatically available once the plugins are active:

**On Local Server (Chapter Transcription Manager):**
- `GET /wp-json/chapter-transcription/v1/embeddings/export` - Export embeddings in batches
- `GET /wp-json/chapter-transcription/v1/embeddings/count` - Get total count of embeddings

**On WPEngine Server (Academy AI Assistant):**
- `POST /wp-json/academy-ai-assistant/v1/embeddings/import` - Import embeddings data
- `GET /wp-json/academy-ai-assistant/v1/embeddings/count` - Get total count of embeddings

**Authentication Required:** Administrator access (`manage_options` capability)

## Setup Instructions

### Step 1: Configure Migration Script

Edit `migrate-embeddings.php` and update the configuration constants:

```php
define('SOURCE_URL', 'http://localhost');  // Your LOCAL server URL (where database is)
define('DEST_URL', 'https://yoursite.wpengine.com');  // WPEngine destination URL
define('SOURCE_AUTH_USER', 'admin');  // WordPress admin username for source
define('SOURCE_AUTH_PASS', 'your-application-password-here');  // Application password for source
define('DEST_AUTH_USER', 'admin');  // WordPress admin username for destination
define('DEST_AUTH_PASS', 'your-application-password-here');  // Application password for destination
define('BATCH_SIZE', 100);  // Number of records per batch (adjust based on server capacity)
define('OVERWRITE_EXISTING', false);  // Set to true to overwrite existing records
```

### Step 2: Ensure Plugins are Active on Both Servers

Make sure the appropriate plugins are active:
- **Local server**: `chapter-transcription` plugin (Chapter Transcription Manager) - provides export endpoints
- **WPEngine server**: `academy-ai-assistant` plugin - provides import endpoints

The REST API endpoints are automatically available once the plugins are active.

### Step 3: Create Table on WPEngine (if needed)

If the `wp_alm_transcript_embeddings` table doesn't exist on WPEngine yet, you can create it using the SQL file:
- `plugins/academy-ai-assistant/create-embeddings-table.sql`
- Or use the table structure from `plugins/chapter-transcription/add-embeddings-table.sql`

### Step 4: Place Migration Script on Local Server

Upload `migrate-embeddings.php` to the root of your WordPress installation on your **local server** (where the database is).

### Step 5: Run Migration

#### Option A: Command Line (Recommended)

From your local server, run:
```bash
cd /path/to/wordpress
php migrate-embeddings.php
```

#### Option B: Web Browser

1. Navigate to: `http://localhost/migrate-embeddings.php` (or your local URL)
2. You must be logged in as an administrator on the local WordPress site

## API Endpoints Reference

### Export Embeddings

**Endpoint:** `GET /wp-json/academy-ai-assistant/v1/embeddings/export`

**Parameters:**
- `batch_size` (integer, optional): Number of records per batch (default: 100, max: 1000)
- `offset` (integer, optional): Starting offset (default: 0)
- `transcript_id` (integer, optional): Filter by specific transcript ID (default: 0 = all)

**Example (from local server):**
```bash
curl -u "username:application_password" \
  "http://localhost/wp-json/chapter-transcription/v1/embeddings/export?batch_size=100&offset=0"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "transcript_id": 123,
      "segment_index": 0,
      "embedding": "[0.123, 0.456, ...]",
      "segment_text": "Hello world",
      "start_time": 0.0,
      "end_time": 5.5,
      "created_at": "2024-01-01 12:00:00"
    }
  ],
  "pagination": {
    "total": 1000,
    "offset": 0,
    "batch_size": 100,
    "returned": 100,
    "has_more": true
  }
}
```

### Import Embeddings

**Endpoint:** `POST /wp-json/academy-ai-assistant/v1/embeddings/import`

**Body:**
```json
{
  "embeddings": [
    {
      "transcript_id": 123,
      "segment_index": 0,
      "embedding": "[0.123, 0.456, ...]",
      "segment_text": "Hello world",
      "start_time": 0.0,
      "end_time": 5.5,
      "created_at": "2024-01-01 12:00:00"
    }
  ],
  "overwrite": false
}
```

**Response:**
```json
{
  "success": true,
  "summary": {
    "total": 100,
    "inserted": 95,
    "updated": 0,
    "skipped": 5,
    "errors": 0
  },
  "errors": []
}
```

### Get Embeddings Count

**Endpoint:** `GET /wp-json/academy-ai-assistant/v1/embeddings/count`

**Example (to WPEngine):**
```bash
curl -u "username:application_password" \
  "https://yoursite.wpengine.com/wp-json/academy-ai-assistant/v1/embeddings/count"
```

**Response:**
```json
{
  "success": true,
  "total_count": 1000,
  "unique_transcripts": 50,
  "transcript_counts": [
    {
      "transcript_id": 123,
      "segment_count": 20
    }
  ]
}
```

## Manual Migration (Alternative)

If you prefer to write your own migration script, you can use the REST API endpoints directly:

```php
// Example: Export from local server (Chapter Transcription Manager)
$source_url = 'http://localhost/wp-json/chapter-transcription/v1/embeddings/export';
$ch = curl_init($source_url . '?batch_size=100&offset=0');
curl_setopt($ch, CURLOPT_USERPWD, 'username:application_password');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$data = json_decode($response, true);

// Example: Import to WPEngine
$dest_url = 'https://yoursite.wpengine.com/wp-json/academy-ai-assistant/v1/embeddings/import';
$ch = curl_init($dest_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_USERPWD, 'username:application_password');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
    'embeddings' => $data['data'],
    'overwrite' => false
)));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
```

## Troubleshooting

### Authentication Errors

- **401 Unauthorized**: Check that Application Passwords are correctly configured
- **403 Forbidden**: Ensure the user has `manage_options` capability (Administrator role)

### Table Not Found Errors

- Ensure the `wp_alm_transcript_embeddings` table exists on both servers
- Check table prefix matches (default is `wp_`)

### Performance Issues

- Reduce `BATCH_SIZE` if you encounter timeouts
- Increase PHP `max_execution_time` if needed
- Consider running during off-peak hours

### Data Validation

- The script validates required fields (`transcript_id`, `segment_index`)
- Missing or invalid data will be skipped and reported in errors

## Security Notes

1. **Application Passwords** are more secure than regular passwords for API access
2. **HTTPS** is recommended for all API calls
3. The endpoints require **administrator authentication**
4. Consider using **IP whitelisting** on WPEngine for additional security

## Post-Migration Verification

After migration completes:

1. Check the final count on destination matches source
2. Verify a few sample records manually
3. Test embedding search functionality to ensure data integrity

## Support

If you encounter issues:
1. Check WordPress debug logs
2. Verify REST API is enabled on both servers
3. Test endpoints manually using curl or Postman
4. Check that both servers have the `academy-ai-assistant` plugin active

