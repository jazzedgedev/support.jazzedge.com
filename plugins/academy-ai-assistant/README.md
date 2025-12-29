# Academy AI Assistant

AI-powered chat assistants with multiple personality types to help piano students learn. Provides contextual assistance using lesson transcripts, embeddings, and student progress data.

## Features

- **Multiple AI Personalities**: Coach, Study Buddy, Professor, Mentor, Practice Assistant, Cheerleader
- **Contextual Assistance**: Uses lesson transcripts, embeddings, and student progress
- **Debug & Testing System**: Feature flags and user whitelist for controlled testing
- **Secure & Isolated**: Never modifies existing plugins or their data

## Requirements

- WordPress 5.0+
- PHP 7.4+
- **Required**: Katahdin AI Hub plugin
- **Optional**: academy-lesson-manager, chapter-transcription, academy-practice-hub-dash

## Installation

1. Upload the `academy-ai-assistant` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings in **AI Assistant > Settings**

## Configuration

### Feature Flags & Testing

1. Go to **AI Assistant > Settings**
2. Configure test user IDs (comma-separated) for testing
3. Enable "Enable for All Users" when ready for production
4. Enable "Debug Mode" to see debug logs and frontend debug panel

### Debug System

- **Test User Whitelist**: Only specified user IDs can access AI features
- **Debug Logging**: All AI interactions are logged (when debug mode enabled)
- **Debug Panel**: Test users can see debug info in frontend (when debug mode enabled)
- **Admin Dashboard**: View all debug logs in **AI Assistant > Debug Logs**

## Security

- All user input is sanitized and validated
- REST API endpoints require authentication and nonces
- Database queries use prepared statements
- Only authorized users can access AI features
- Debug logs never contain sensitive data (API keys, passwords)

## Performance

- Database queries optimized with proper indexes
- Context data cached for 5 minutes
- Embedding searches limited to top 10 results
- Conversation history paginated (50 messages per page)

## Development Status

**Phase 1: Plugin Foundation** ✅ COMPLETE
- Plugin structure created
- Database tables created
- Feature flags system implemented
- Debug logging system implemented
- Admin interface created

**Phase 2-8**: In progress

## Support

For support and questions, contact the JazzEdge development team.

