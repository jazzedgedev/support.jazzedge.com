# Bunny Video Webhook Plugin

A WordPress plugin that automatically captures Bunny.net video uploads via webhook and updates the lesson database with sample video URLs.

## Features

- **Automatic Processing**: Captures video uploads from Bunny.net via webhook
- **Filename Parsing**: Extracts lesson ID and chapter ID from video filenames
- **Database Updates**: Automatically updates `wp_alm_lessons` table with HLS Playlist URLs
- **Smart Filtering**: Only processes videos with "-sample" in the filename
- **Comprehensive Logging**: Logs all events for manual review and debugging
- **Admin Interface**: Easy-to-use settings page and logs viewer

## Installation

1. Upload the `bunny-video-webhook` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Bunny Webhook > Settings** to configure

## Configuration

### 1. Webhook URL

The plugin provides a webhook URL that you'll need to configure in your Bunny.net account:

```
https://yoursite.com/wp-json/bunny-video-webhook/v1/webhook
```

### 2. Webhook Secret (Optional)

For security, you can set a webhook secret. If configured, Bunny.net must send this secret in:
- Header: `X-Webhook-Secret`
- OR Query parameter: `?secret=your-secret`

### 3. CDN Hostname

Configure your Bunny.net CDN hostname (e.g., `vz-0696d3da-4b7.b-cdn.net`). If left empty, a default will be used.

## Filename Format

Videos must follow this naming convention to be processed:

```
{lesson-id}-{chapter-id}-id{chapter-id}-{title}-sample.mp4
```

**Example:**
```
78-797-id797-Finding-Scale-Secrets-sample.mp4
```

- **First number (78)** = Lesson ID (maps to `wp_alm_lessons.ID`)
- **Second number (797)** = Chapter ID (stored in `sample_chapter_id`)
- **Must contain "-sample"** in the filename

## How It Works

1. Bunny.net sends a webhook when a video is uploaded
2. Plugin checks if filename contains "-sample"
3. Filename is parsed to extract lesson ID and chapter ID
4. HLS Playlist URL is constructed: `https://{cdn-hostname}/{video-id}/playlist.m3u8`
5. Database is updated:
   - `sample_video_url` = HLS Playlist URL
   - `sample_chapter_id` = Chapter ID
6. All events are logged for review

## Database Updates

The plugin updates the following fields in `wp_alm_lessons`:

- `sample_video_url` - HLS Playlist URL (e.g., `https://vz-0696d3da-4b7.b-cdn.net/{video-id}/playlist.m3u8`)
- `sample_chapter_id` - Chapter ID extracted from filename

## Logging

All webhook events are logged with the following information:

- **Log Type**: success, error, info, warning
- **Message**: Description of the event
- **Video ID**: Bunny.net video ID
- **Video Filename**: Original filename
- **Lesson ID**: Extracted lesson ID
- **Chapter ID**: Extracted chapter ID
- **Bunny URL**: Generated HLS URL
- **Webhook Data**: Full webhook payload
- **Error Details**: Error information (if applicable)

View logs in **Bunny Webhook > Logs**

## Error Handling

If an error occurs (e.g., lesson not found, invalid filename), the plugin:

1. Logs the error with full details
2. Returns an error response to Bunny.net
3. Makes the error available for manual review in the logs

## Testing

Use the test endpoint to verify your setup:

```
POST /wp-json/bunny-video-webhook/v1/test
```

Requires admin authentication.

## Webhook Payload

The plugin expects Bunny.net to send webhook data with at least one of:

- `VideoId` or `videoId` - The Bunny.net video ID
- `Title` or `title` - Video title (used as filename if FileName not provided)
- `FileName` or `fileName` - Video filename

## Requirements

- WordPress 5.0+
- PHP 7.4+
- `wp_alm_lessons` table must exist (from Academy Lesson Manager plugin)

## Support

For issues or questions, check the logs in **Bunny Webhook > Logs** for detailed error information.

