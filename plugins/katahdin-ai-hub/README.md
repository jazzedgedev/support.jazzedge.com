# Katahdin AI Hub

A centralized AI integration hub for all Katahdin AI-powered WordPress plugins. Manages API keys, usage quotas, and provides unified AI services.

**Powered by [Katahdin AI](https://katahdin.ai/)**

## Features

- **Centralized API Management**: Single OpenAI API key storage and management
- **Plugin Registry**: Register and manage multiple AI-powered plugins
- **Usage Tracking**: Monitor API usage, costs, and performance metrics
- **Quota Management**: Set and enforce usage limits per plugin
- **REST API**: Modern REST endpoints for AI services
- **Admin Dashboard**: Comprehensive management interface
- **Security**: Encrypted API key storage and secure authentication
- **Analytics**: Detailed usage analytics and cost tracking

## Installation

1. Upload the plugin files to `/wp-content/plugins/katahdin-ai-hub/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Katahdin AI Hub > API Settings to configure your OpenAI API key
4. Register your plugins with the hub

## Configuration

### API Key Setup

1. Get your OpenAI API key from [OpenAI Platform](https://platform.openai.com/api-keys)
2. Go to Katahdin AI Hub > API Settings in your WordPress admin
3. Enter your API key and click "Test API Connection" to verify it works
4. The API key is securely encrypted and stored in the WordPress options table

### Plugin Registration

Plugins can register with the hub using the following code:

```php
// In your plugin's initialization
add_action('katahdin_ai_hub_init', function($hub) {
    $hub->register_plugin('your-plugin-id', array(
        'name' => 'Your Plugin Name',
        'version' => '1.0.0',
        'features' => array('chat', 'completions', 'embeddings'),
        'quota_limit' => 1000 // tokens per month
    ));
});
```

### Making API Calls

Use the hub to make API calls from your plugins:

```php
// Chat completions
$response = katahdin_ai_hub()->make_api_call('your-plugin-id', 'chat/completions', array(
    'messages' => array(
        array('role' => 'user', 'content' => 'Hello, world!')
    )
), array(
    'model' => 'gpt-3.5-turbo',
    'max_tokens' => 1000
));

// Text completions
$response = katahdin_ai_hub()->make_api_call('your-plugin-id', 'completions', array(
    'prompt' => 'Complete this sentence:'
), array(
    'model' => 'text-davinci-003',
    'max_tokens' => 500
));

// Embeddings
$response = katahdin_ai_hub()->make_api_call('your-plugin-id', 'embeddings', array(
    'input' => 'Text to embed'
), array(
    'model' => 'text-embedding-ada-002'
));
```

## REST API Endpoints

The hub provides REST API endpoints for AI services:

### Chat Completions
```
POST /wp-json/katahdin-ai-hub/v1/chat/completions
Headers: X-Plugin-ID: your-plugin-id
Body: {
    "messages": [{"role": "user", "content": "Hello"}],
    "model": "gpt-3.5-turbo",
    "max_tokens": 1000
}
```

### Text Completions
```
POST /wp-json/katahdin-ai-hub/v1/completions
Headers: X-Plugin-ID: your-plugin-id
Body: {
    "prompt": "Complete this:",
    "model": "text-davinci-003",
    "max_tokens": 500
}
```

### Embeddings
```
POST /wp-json/katahdin-ai-hub/v1/embeddings
Headers: X-Plugin-ID: your-plugin-id
Body: {
    "input": "Text to embed",
    "model": "text-embedding-ada-002"
}
```

### Usage Statistics
```
GET /wp-json/katahdin-ai-hub/v1/usage?days=30
Headers: X-Plugin-ID: your-plugin-id
```

### Quota Status
```
GET /wp-json/katahdin-ai-hub/v1/quota
Headers: X-Plugin-ID: your-plugin-id
```

## Admin Interface

The plugin provides a comprehensive admin interface:

- **Dashboard**: Overview of API status, usage statistics, and registered plugins
- **API Settings**: Configure OpenAI API key and global settings
- **Usage Analytics**: Detailed analytics on API usage, costs, and performance
- **Plugin Registry**: Manage registered plugins and their quotas

## Database Schema

The plugin creates the following database tables:

### wp_katahdin_ai_plugins
- `id`: Primary key
- `plugin_id`: Unique plugin identifier
- `plugin_name`: Human-readable plugin name
- `version`: Plugin version
- `features`: JSON array of supported features
- `quota_limit`: Monthly token limit
- `quota_used`: Current month's token usage
- `is_active`: Plugin status
- `registered_at`: Registration timestamp
- `last_used`: Last API call timestamp

### wp_katahdin_ai_usage
- `id`: Primary key
- `plugin_id`: Plugin identifier
- `endpoint`: API endpoint used
- `tokens_used`: Tokens consumed
- `cost`: Cost of the API call
- `response_time`: Response time in milliseconds
- `success`: Success status
- `error_message`: Error message if failed
- `created_at`: Timestamp

### wp_katahdin_ai_logs
- `id`: Primary key
- `plugin_id`: Plugin identifier
- `level`: Log level (info, warning, error)
- `message`: Log message
- `context`: JSON context data
- `created_at`: Timestamp

## Security Features

- **Encrypted API Keys**: API keys are encrypted using WordPress salts
- **Permission Checks**: All endpoints require proper authentication
- **Rate Limiting**: Configurable rate limiting per plugin
- **Quota Enforcement**: Automatic quota checking and enforcement
- **Input Validation**: All inputs are validated and sanitized

## Usage Monitoring

The hub tracks:
- API usage per plugin
- Token consumption
- Cost analysis
- Response times
- Success/failure rates
- Error analysis

## Migration from Individual Plugins

To migrate existing plugins to use the Katahdin AI Hub:

1. **Remove individual API code** from your plugin
2. **Register with the hub** using the registration code above
3. **Replace API calls** with hub calls
4. **Test thoroughly** to ensure functionality

## Support

For support and documentation, visit [Katahdin AI](https://katahdin.ai/).

## Changelog

### 1.0.0
- Initial release
- Centralized API management
- Plugin registry system
- Usage tracking and analytics
- REST API endpoints
- Admin dashboard
- Security features

---

*Built with ❤️ by Katahdin AI for the WordPress community.*
