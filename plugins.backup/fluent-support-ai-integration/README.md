# Fluent Support AI Integration

A WordPress plugin that integrates OpenAI's AI capabilities into the Fluent Support ticket system for automated reply generation.

**Powered by [Katahdin AI](https://katahdin.ai/)**

![Katahdin AI Logo](https://katahdin.ai/wp-content/uploads/2025/09/cropped-Katahdin-AI-Logo-dark-with-tag.png)

## Features

- **OpenAI Integration**: Seamlessly integrate with OpenAI's GPT models
- **Custom Prompts**: Create and manage custom AI prompts for different scenarios
- **Secure API Key Management**: Safely store and test OpenAI API keys
- **Ticket Analysis**: AI analyzes ticket content and generates contextual responses
- **Easy Integration**: Simple dropdown and button interface in ticket reply forms
- **Security First**: Built with WordPress security best practices

## Installation

1. Upload the plugin files to `/wp-content/plugins/fluent-support-ai-integration/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Fluent Support > AI Integration to configure your OpenAI API key
4. Create custom prompts for your use cases

## Configuration

### API Key Setup

1. Get your OpenAI API key from [OpenAI Platform](https://platform.openai.com/api-keys)
2. Go to Fluent Support > AI Integration in your WordPress admin
3. Enter your API key and click "Test API Key" to verify it works
4. The API key is securely stored in the WordPress options table

### Creating Prompts

1. In the AI Integration settings page, scroll to "AI Prompts Management"
2. Fill in the prompt form:
   - **Prompt Name**: A descriptive name for the prompt
   - **Description**: Brief description of when to use this prompt
   - **Prompt Content**: The AI prompt template (must include `{ticket_content}`)
3. Click "Add Prompt" to save

### Using AI Replies

1. When replying to a ticket, you'll see the "AI Reply Assistant" section
2. Select a prompt from the dropdown
3. Click "Generate AI Reply" to create an AI-generated response
4. Review and edit the generated response before sending

## Default Prompts

The plugin comes with three default prompts:

1. **Professional Response**: General professional customer support response
2. **Technical Support**: Technical support with troubleshooting steps
3. **Escalation Response**: Response for escalated customer issues

## Security Features

- **Nonce Protection**: All AJAX requests are protected with WordPress nonces
- **Capability Checks**: Only users with appropriate permissions can access features
- **Input Sanitization**: All user inputs are sanitized and validated
- **Secure Storage**: API keys are stored securely in WordPress options table
- **CSRF Protection**: Forms are protected against cross-site request forgery

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Fluent Support plugin
- OpenAI API key
- Internet connection for API calls

## Hooks and Filters

The plugin integrates with Fluent Support using the following hooks:

- `fluent_support_after_ticket_reply_form`: Adds AI interface to reply forms

## Customization

### Adding Custom Hooks

You can extend the plugin functionality using WordPress hooks:

```php
// Modify AI response before insertion
add_filter('fluent_support_ai_response_content', function($content, $ticket_id, $prompt_id) {
    // Modify the content
    return $content;
}, 10, 3);

// Add custom validation
add_filter('fluent_support_ai_validate_prompt', function($is_valid, $prompt_content) {
    // Add custom validation logic
    return $is_valid;
}, 10, 2);
```

### Styling

The plugin includes CSS classes you can customize:

- `.fluent-support-ai-interface`: Main AI interface container
- `.ai-prompt-dropdown`: Prompt selection dropdown
- `#generate-ai-reply`: Generate button
- `.ai-loading`: Loading state
- `.ai-message`: Success/error messages

## Troubleshooting

### Common Issues

1. **API Key Not Working**
   - Verify your API key is correct
   - Check if you have sufficient OpenAI credits
   - Ensure your server can make HTTPS requests

2. **AI Reply Not Generating**
   - Check browser console for JavaScript errors
   - Verify the ticket has content to analyze
   - Ensure the selected prompt contains `{ticket_content}`

3. **Interface Not Showing**
   - Ensure you're on a ticket reply page
   - Check if you have the required capabilities
   - Verify the plugin is active and Fluent Support is installed

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For support and feature requests, please contact the plugin developer or create an issue in the plugin repository.

## Changelog

### Version 1.0.0
- Initial release
- OpenAI integration
- Custom prompt management
- AI reply generation
- Security features
- Responsive design

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- **Powered by [Katahdin AI](https://katahdin.ai/)** - Advanced AI solutions for WordPress applications
- Built for Fluent Support
- Uses OpenAI's GPT models
- Follows WordPress coding standards
- Implements security best practices

## About Katahdin AI

This integration showcases the power of [Katahdin AI](https://katahdin.ai/) in creating sophisticated WordPress plugins that seamlessly integrate AI capabilities into existing workflows. Katahdin AI specializes in:

- Custom AI integrations for WordPress
- Advanced prompt engineering
- Secure API implementations
- User-friendly AI interfaces
- Production-ready AI solutions

Visit [Katahdin AI](https://katahdin.ai/) to learn more about our AI development services.
