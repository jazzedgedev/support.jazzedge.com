# Academy Analytics

A simple WordPress analytics plugin for tracking form submissions and page visits via webhook with a full CRUD interface and reporting system.

## Features

- **Webhook Integration**: Receive analytics data from Flowmattic or any external service
- **Flexible Data Storage**: Store any fields you want - common fields in columns, everything else in JSON
- **Full CRUD Interface**: Create, Read, Update, and Delete events through the admin interface
- **Advanced Reporting**: View statistics with time frame filters (past day, 7, 14, 30, 90 days, or custom range)
- **Filtering & Search**: Filter events by type, email, form name, page URL, and date range
- **Bulk Operations**: Delete multiple events at once

## Installation

1. Upload the `academy-analytics` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Analytics > Settings** to get your webhook URL

## Database Schema

The plugin creates a single table `wp_academy_analytics_events` with the following structure:

### Standard Columns (for fast querying):
- `id` - Primary key
- `event_type` - Type of event (form_submission, page_visit, etc.)
- `user_id` - WordPress user ID (if available)
- `email` - User email address
- `form_name` - Name/ID of the form
- `page_title` - Title of the page
- `page_url` - Full URL of the page
- `referrer` - HTTP referrer URL
- `ip_address` - Client IP address
- `created_at` - Timestamp

### JSON Column:
- `data` - Stores all additional fields from the webhook payload as JSON

## Integration Methods

There are two ways to send events to Academy Analytics:

### Method 1: Direct PHP Function (Recommended - Faster)

If Flowmattic can call PHP functions directly, use this method. It's faster because it bypasses HTTP/REST API.

**Function Name:** `academy_analytics_record_event()`

**Flowmattic Setup:**
1. In Flowmattic, add a "PHP Function" action
2. Function name: `academy_analytics_record_event`
3. Pass your data as an array parameter

**Example PHP Code:**
```php
<?php
$result = academy_analytics_record_event(array(
    'event_type' => 'form_submission',
    'email' => 'user@example.com',
    'form_name' => 'Contact Form',
    'data' => array(
        'name' => 'John Doe',
        'phone' => '555-1234',
        'message' => 'Hello world'
    )
));

// Check result
if (is_wp_error($result)) {
    error_log('Analytics error: ' . $result->get_error_message());
} else {
    // Success - event_id is in $result['event_id']
}
?>
```

**Return Value:**
- On success: Returns array with `success`, `event_id`, and `message`
- On error: Returns `WP_Error` object

### Method 2: Webhook (HTTP/REST API)

Use this method when Flowmattic cannot call PHP functions directly, or when calling from external services.

**Webhook URL:**
```
https://yoursite.com/wp-json/academy-analytics/v1/webhook
```

Your webhook URL is available in **Analytics > Settings**.

**Authentication (Optional):**

You can set a webhook secret in the settings page. Include it either:
- As a header: `X-Webhook-Secret: your-secret-key`
- As a parameter: `?secret=your-secret-key` or in the JSON body

**Flowmattic Setup:**
1. In Flowmattic, add a "Webhook" action
2. Set method to **POST**
3. Enter the webhook URL
4. Configure the payload with your data

### Example Payloads

#### Form Submission
```json
{
  "event_type": "form_submission",
  "email": "user@example.com",
  "form_name": "Contact Form",
  "user_id": 123,
  "data": {
    "name": "John Doe",
    "phone": "555-1234",
    "message": "I have a question",
    "custom_field": "value"
  }
}
```

#### Page Visit
```json
{
  "event_type": "page_visit",
  "email": "user@example.com",
  "page_title": "Jazz Piano Lessons",
  "page_url": "https://academy.jazzedge.com/lessons/jazz-piano",
  "referrer": "https://google.com",
  "ip_address": "192.168.1.1",
  "data": {
    "session_id": "xyz789",
    "device": "desktop",
    "browser": "Chrome",
    "duration_seconds": 45
  }
}
```

### Comparison

| Method | Speed | Use Case |
|--------|-------|----------|
| **PHP Function** | Faster (direct call) | Flowmattic on same server |
| **Webhook** | Slower (HTTP request) | External services or when PHP functions not available |

**Recommendation:** Use the PHP function method if available, as it's faster and more efficient.

## Admin Interface

### Events List

Navigate to **Analytics > Events** to view all tracked events.

**Features:**
- Filter by event type, email, form name, page URL, and date range
- Search across multiple fields
- Bulk delete operations
- Pagination
- Quick view/edit/delete actions

### Reports

Navigate to **Analytics > Reports** to view analytics statistics.

**Time Frames:**
- Past Day
- Past 7 Days
- Past 14 Days
- Past 30 Days
- Past 90 Days
- Custom Range

**Statistics Shown:**
- Total Events
- Unique Users
- Unique Emails
- Events by Type
- Top Forms (by submission count)
- Top Pages (by visit count)

### Settings

Navigate to **Analytics > Settings** to configure:
- Webhook URL (display only)
- Webhook Secret (optional authentication)

## CRUD Operations

### Create Event
1. Go to **Analytics > Events**
2. Click **Add New** (or use the webhook)
3. Fill in the form fields
4. Click **Add Event**

### Read/View Event
1. Go to **Analytics > Events**
2. Click **View** on any event
3. See all event details including JSON data

### Update Event
1. Go to **Analytics > Events**
2. Click **Edit** on any event
3. Modify the fields
4. Click **Update Event**

### Delete Event
1. Go to **Analytics > Events**
2. Click **Delete** on any event (or use bulk actions)
3. Confirm deletion

## Data Structure

The plugin uses a hybrid approach:
- **Common fields** are stored in dedicated columns for fast querying
- **All other data** is stored in the JSON `data` column

This gives you:
- Fast queries on frequently used fields
- Flexibility to store any additional data without schema changes
- Easy access to all data in the admin interface

## Hooks & Filters

### Actions
- `academy_analytics_event_inserted` - Fired after an event is inserted
  - Parameters: `$event_id`, `$event_data`

### Filters
- `academy_analytics_event_data` - Filter event data before insertion
  - Parameters: `$event_data`, `$raw_data`

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Support

For issues or questions, please contact the development team.

## Changelog

### 1.0.0
- Initial release
- Webhook endpoint for receiving events
- Full CRUD interface
- Reporting with time frame filters
- Flexible JSON data storage

