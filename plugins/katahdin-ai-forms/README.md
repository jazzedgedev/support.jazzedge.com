# Katahdin AI Webhook Plugin

A WordPress plugin that creates a webhook endpoint to receive FluentForm submissions, analyze them using OpenAI AI, and send the analysis results via email.

## Features

- **Webhook Endpoint**: Secure REST API endpoint to receive form submissions
- **AI Analysis**: Integrates with Katahdin AI Hub for OpenAI API access
- **Email Notifications**: Sends formatted analysis results to configured email addresses
- **Admin Interface**: Easy-to-use settings page for configuration
- **Security**: Webhook secret authentication for secure data transmission
- **Testing Tools**: Built-in testing functionality for webhook and email
- **Activity Logs**: Recent activity tracking for debugging and monitoring

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Katahdin AI Hub plugin (for OpenAI API access)
- FluentForm plugin (for form submissions)

## Installation

1. Upload the plugin files to `/wp-content/plugins/katahdin-ai-webhook/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure Katahdin AI Hub is installed and configured with OpenAI API key
4. Go to Tools > AI Webhook to configure settings

## Configuration

### General Settings

- **Enable Webhook**: Toggle webhook processing on/off
- **Webhook URL**: The endpoint URL for FluentForm integration
- **Secret Key**: Authentication key for secure webhook calls

### AI Settings

- **AI Prompt**: Customizable prompt sent to OpenAI along with form data
- **AI Model**: Choose between GPT-3.5 Turbo, GPT-4, or GPT-4 Turbo
- **Max Tokens**: Maximum response length (1-4000 tokens)
- **Temperature**: Controls response randomness (0-2)

### Email Settings

- **Email Address**: Recipient for analysis results
- **Email Subject**: Subject line for analysis emails

## FluentForm Integration

### Webhook Configuration

1. In FluentForm, go to your form's Settings
2. Navigate to Integrations > Webhooks
3. Add new webhook with these settings:

**Webhook URL:**
```
https://yoursite.com/wp-json/katahdin-ai-webhook/v1/webhook
```

**Headers:**
```
X-Webhook-Secret: your_secret_key_here
Content-Type: application/json
```

**Payload Format:**
```json
{
    "form_data": {
        "field_name": "field_value",
        "email": "user@example.com",
        "message": "User message"
    },
    "form_id": "your_form_id",
    "entry_id": "entry_id"
}
```

### Dynamic Field Mapping

Map your FluentForm fields to the webhook payload:

```json
{
    "form_data": {
        "name": "{inputs.name}",
        "email": "{inputs.email}",
        "message": "{inputs.message}",
        "phone": "{inputs.phone}"
    },
    "form_id": "{form.id}",
    "entry_id": "{entry.id}"
}
```

## API Endpoints

### Webhook Endpoint
- **URL**: `/wp-json/katahdin-ai-webhook/v1/webhook`
- **Method**: POST
- **Authentication**: X-Webhook-Secret header
- **Content-Type**: application/json

### Test Endpoint
- **URL**: `/wp-json/katahdin-ai-webhook/v1/test`
- **Method**: POST
- **Authentication**: WordPress admin user
- **Purpose**: Test webhook functionality

## Security

- Webhook secret authentication prevents unauthorized access
- All form data is sanitized before processing
- AI responses are escaped in email output
- Rate limiting through Katahdin AI Hub integration

## Troubleshooting

### Common Issues

1. **Webhook not receiving data**
   - Check webhook URL is correct
   - Verify secret key matches
   - Ensure webhook is enabled in settings

2. **AI analysis failing**
   - Verify Katahdin AI Hub is configured
   - Check OpenAI API key is valid
   - Review quota limits in AI Hub

3. **Emails not sending**
   - Check email address is valid
   - Verify WordPress mail configuration
   - Test email functionality in admin

### Debug Mode

Enable debug logging in Katahdin AI Hub to troubleshoot API issues.

### Activity Logs

Recent webhook activity is logged and visible in the admin interface under "Recent Activity".

## Customization

### Custom AI Prompts

Modify the AI prompt in the admin settings to customize analysis behavior:

```
Analyze this customer inquiry and provide:
1. Sentiment analysis
2. Priority level (High/Medium/Low)
3. Recommended response
4. Key topics mentioned

Form Data:
{form_data}
```

### Email Templates

Email templates can be customized by modifying the `prepare_email_content()` method in the Email Sender class.

## Support

For support and documentation, visit [Katahdin AI](https://katahdin.ai).

## Changelog

### Version 1.0.0
- Initial release
- Webhook endpoint for FluentForm integration
- AI analysis using OpenAI
- Email notifications
- Admin interface
- Security features
- Testing tools

## License

GPL v2 or later
