# Keap To Fluent Tagging

A WordPress plugin that provides an HTTP POST endpoint to tag FluentCRM contacts from Keap (formerly Infusionsoft).

## Features

- REST API endpoint for receiving POST requests from Keap
- Authentication via configurable code
- Automatic contact creation if contact doesn't exist in FluentCRM
- Tag contacts with FluentCRM tags based on tag ID

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- FluentCRM plugin installed and activated

## Installation

1. Upload the `keap-to-fluent-tagging` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Keap To Fluent Tagging to configure

## Configuration

1. Navigate to **Settings > Keap To Fluent Tagging**
2. Enter an authentication code (this will be sent by Keap in the `code` field)
3. Save settings
4. Copy the endpoint URL shown on the settings page

## Endpoint Details

**URL:** `https://yoursite.com/wp-json/ktf/v1/tag`  
**Method:** `POST`  
**Content-Type:** `application/json`

### Request Body

```json
{
  "email": "user@example.com",
  "tag_id": 123,
  "code": "your-authentication-code"
}
```

### Response (Success)

```json
{
  "success": true,
  "message": "Contact tagged successfully",
  "contact_id": 456,
  "email": "user@example.com",
  "tag_id": 123
}
```

### Response (Error)

```json
{
  "code": "error_code",
  "message": "Error message",
  "data": {
    "status": 400
  }
}
```

## Keap Webhook Setup

1. In Keap, create a webhook that triggers on your desired event
2. Set the webhook URL to: `https://yoursite.com/wp-json/ktf/v1/tag`
3. Configure the webhook to send POST requests with JSON body containing:
   - `email`: Contact email address
   - `tag_id`: FluentCRM tag ID (numeric)
   - `code`: Your configured authentication code

## How It Works

1. Keap sends a POST request to the endpoint with `email`, `tag_id`, and `code`
2. Plugin validates the authentication code
3. Plugin checks if contact exists in FluentCRM by email
4. If contact doesn't exist, it creates a new contact with status "subscribed"
5. Plugin applies the specified tag to the contact
6. Returns success or error response

## Error Codes

- `missing_email` - Email field is required
- `missing_tag_id` - tag_id field is required
- `missing_code` - code field is required
- `code_not_configured` - Authentication code not configured in plugin settings
- `invalid_code` - Invalid authentication code
- `invalid_email` - Invalid email format
- `invalid_tag_id` - tag_id must be a positive integer
- `fluentcrm_not_available` - FluentCRM is not available
- `contact_error` - Failed to get or create contact
- `tagging_failed` - Failed to tag contact

## Security

- All requests are authenticated via the configurable code
- Email addresses are sanitized
- Tag IDs are validated as positive integers
- Admin settings page requires `manage_options` capability

## Version

1.0.0

