# Enable WordPress Debug Logging

To see detailed transcription progress and debug information, add these lines to your `wp-config.php` file.

## Instructions

1. Open your `wp-config.php` file (usually in the WordPress root directory)

2. Find this section:
```php
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}
```

3. Replace it with:
```php
// Enable WordPress debugging
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false ); // Don't show errors on frontend
```

4. Save the file

## Where to Find Logs

After enabling logging, WordPress will create a `debug.log` file at:
```
/wp-content/debug.log
```

## What You'll See

The log file will contain entries like:
- `Transcription Status [Chapter X]: PROCESSING - Validating file...`
- `Transcription attempt 1/3 for chapter X: HTTP 200, Duration: 45s`
- `Bulk download: Processing chapter X (1 of 7)`

## Viewing Logs

You can:
1. View logs directly in the admin page (click "📋 View Logs" button on active transcriptions)
2. Open the file directly: `/wp-content/debug.log`
3. Use your Local by Flywheel file browser to view the log file

## Important Notes

- **Local Development Only**: Only enable this on your local development site, NOT on production
- **File Size**: The log file can grow large over time, so periodically delete it or rotate it
- **Security**: Make sure `debug.log` is not publicly accessible (WordPress should handle this, but double-check)

