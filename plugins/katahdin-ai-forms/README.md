# Katahdin AI Forms

A commercial-grade WordPress plugin that processes form submissions using AI analysis and sends results via email with per-prompt configuration.

## Features

- **AI-Powered Form Processing**: Analyze form submissions using OpenAI models
- **Per-Prompt Email Settings**: Each prompt has its own email address and subject
- **Prompt ID System**: Forms specify which prompt to use via `prompt_id` parameter
- **Comprehensive Logging**: Track all form submissions and AI analysis results
- **Security First**: Input sanitization, nonce verification, capability checks
- **Commercial Ready**: Error handling, debug logging, performance optimized
- **WordPress Integration**: Full REST API, admin interface, settings management

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Katahdin AI Hub plugin (for OpenAI API access)

## Installation

1. Upload the plugin files to `/wp-content/plugins/katahdin-ai-forms/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure Katahdin AI Hub is installed and configured with your OpenAI API key
4. Configure your first prompt in the admin interface

## Configuration

### 1. General Settings

Navigate to **AI Forms > Settings** to configure:

- **Enable Forms Processing**: Toggle form processing on/off
- **AI Model**: Choose between GPT-3.5 Turbo, GPT-4, or GPT-4 Turbo
- **Max Tokens**: Set maximum tokens for AI responses (100-4000)
- **Temperature**: Control AI creativity (0.0 = focused, 2.0 = creative)
- **Log Retention**: Set how long to keep logs (1-365 days)

### 2. Form Prompts

Navigate to **AI Forms > Form Prompts** to manage prompts:

- **Title**: Descriptive name for the prompt
- **Prompt ID**: Unique identifier (letters, numbers, underscores, hyphens only)
- **AI Prompt**: The instruction text for AI analysis
- **Email Address**: Where to send analysis results
- **Email Subject**: Subject line for the email

### 3. Forms Endpoint

The plugin provides a REST API endpoint for form submissions:

```
POST /wp-json/katahdin-ai-forms/v1/forms
```

**Required Parameters:**
- `prompt_id`: The ID of the prompt to use for analysis

**Optional Parameters:**
- `form_data`: Form submission data (can be in request body)
- `entry_id`: FluentForm entry ID for tracking

**Headers:**
- `X-Webhook-Secret`: Optional webhook secret for authentication

## Usage Examples

### FluentForm Integration

1. Create a new prompt in the admin interface
2. Note the Prompt ID (e.g., `contact_form_analysis`)
3. In FluentForm, add a webhook action with:
   - **Webhook URL**: `https://yoursite.com/wp-json/katahdin-ai-forms/v1/forms`
   - **Method**: POST
   - **Body**: JSON with `prompt_id` and form data

### Example Webhook Payload

```json
{
  "prompt_id": "contact_form_analysis",
  "entry_id": "12345",
  "form_data": {
    "name": "John Doe",
    "email": "john@example.com",
    "message": "I need help with my account"
  }
}
```

### Custom Integration

```php
// Process form data programmatically
$result = katahdin_ai_forms()->process_forms_data($form_data);
```

## API Reference

### REST Endpoints

#### POST /wp-json/katahdin-ai-forms/v1/forms
Process a form submission with AI analysis.

**Parameters:**
- `prompt_id` (string, required): Prompt ID to use
- `form_data` (object, optional): Form data
- `entry_id` (string, optional): Entry ID for tracking

**Response:**
```json
{
  "success": true,
  "message": "Form processed successfully",
  "analysis_id": "analysis_1234567890",
  "email_sent": true,
  "prompt_used": "Contact Form Analysis"
}
```

#### GET /wp-json/katahdin-ai-forms/v1/test-plugin
Test if the plugin is working.

#### GET /wp-json/katahdin-ai-forms/v1/status
Get plugin status information.

#### GET /wp-json/katahdin-ai-forms/v1/debug
Get debug information (admin only).

### Admin AJAX Actions

All admin actions require `manage_options` capability and proper nonce verification:

- `katahdin_ai_forms_test_forms`: Test forms processing
- `katahdin_ai_forms_test_email`: Test email functionality
- `katahdin_ai_forms_add_prompt`: Add new prompt
- `katahdin_ai_forms_update_prompt`: Update existing prompt
- `katahdin_ai_forms_delete_prompt`: Delete prompt
- `katahdin_ai_forms_toggle_prompt`: Toggle prompt active status

## Database Schema

### Forms Logs Table
```sql
CREATE TABLE wp_katahdin_ai_forms_logs (
  id int(11) NOT NULL AUTO_INCREMENT,
  webhook_id varchar(50) NOT NULL,
  status varchar(20) DEFAULT 'received',
  request_data longtext,
  response_code int(11) DEFAULT 200,
  ai_response longtext,
  error_message text,
  email_sent tinyint(1) DEFAULT 0,
  processing_time_ms int(11) DEFAULT 0,
  form_email varchar(255),
  form_name varchar(255),
  form_id varchar(100),
  entry_id varchar(100),
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY webhook_id (webhook_id),
  KEY status (status),
  KEY created_at (created_at),
  KEY form_id (form_id),
  KEY entry_id (entry_id)
);
```

### Form Prompts Table
```sql
CREATE TABLE wp_katahdin_ai_forms_prompts (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  prompt_id varchar(100) NOT NULL,
  prompt text NOT NULL,
  email_address varchar(255) NOT NULL,
  email_subject varchar(255) NOT NULL,
  is_active tinyint(1) DEFAULT 1,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY prompt_id (prompt_id),
  KEY is_active (is_active)
);
```

## Security Features

- **Input Sanitization**: All inputs are sanitized using WordPress functions
- **Nonce Verification**: All AJAX requests use WordPress nonces
- **Capability Checks**: Admin functions require `manage_options` capability
- **SQL Injection Prevention**: All database queries use prepared statements
- **XSS Protection**: All outputs are escaped using WordPress functions
- **CSRF Protection**: Forms use WordPress nonce system

## Error Handling

- **Graceful Degradation**: Plugin continues working even if components fail
- **Debug Logging**: Errors logged only when `WP_DEBUG` is enabled
- **User-Friendly Messages**: Clear error messages for administrators
- **Exception Handling**: All critical operations wrapped in try-catch blocks

## Performance Optimizations

- **Database Indexing**: Proper indexes on frequently queried columns
- **Lazy Loading**: Components loaded only when needed
- **Caching**: WordPress transients for frequently accessed data
- **Optimized Queries**: Efficient database queries with proper limits

## Troubleshooting

### Common Issues

1. **"Katahdin AI Hub not available"**
   - Ensure Katahdin AI Hub plugin is installed and activated
   - Check that the hub plugin is properly configured

2. **"No prompt found for prompt_id"**
   - Verify the prompt ID exists in the admin interface
   - Check that the prompt is active

3. **Email not sending**
   - Verify email address is valid
   - Check WordPress email configuration
   - Test email functionality in admin interface

4. **Forms endpoint not responding**
   - Check that forms processing is enabled
   - Verify webhook secret if using authentication
   - Test endpoint using the admin interface

### Debug Mode

Enable WordPress debug mode to see detailed error logs:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Then check `/wp-content/debug.log` for detailed error information.

## Support

For support and updates, visit [Katahdin AI](https://katahdin.ai).

## Changelog

### Version 1.0.0
- Initial release
- AI-powered form processing
- Per-prompt email configuration
- Comprehensive logging system
- Security and performance optimizations
- Commercial-grade error handling

## License

GPL v2 or later
