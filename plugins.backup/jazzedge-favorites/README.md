# Jazzedge Favorites Plugin

A WordPress plugin that manages lesson favorites and integrates with the Jazzedge Practice Hub.

## Features

- **Shortcode Support**: Add favorite buttons anywhere with `[jazzedge_favorites_button]`
- **REST API**: Full CRUD operations for favorites
- **User Management**: Each user can manage their own favorites
- **Practice Hub Integration**: Seamlessly integrates with the Jazzedge Practice Hub
- **Responsive Design**: Works on all devices

## Installation

1. Upload the `jazzedge-favorites` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create the necessary database tables

## Usage

### Shortcode

Use the shortcode to add a favorites button anywhere:

```php
[jazzedge_favorites_button title="Blues Scales" url="https://example.com/blues" category="Technique" description="Learn basic blues scale patterns"]
```

#### Shortcode Parameters

- `title` - Lesson title (required)
- `url` - Lesson URL (optional)
- `category` - Lesson category (optional)
- `description` - Lesson description (optional)
- `button_text` - Button text (default: "Add to Favorites")
- `class` - CSS class for styling (default: "jf-favorites-btn")

### REST API Endpoints

- `GET /wp-json/jazzedge-favorites/v1/favorites` - Get user's favorites
- `POST /wp-json/jazzedge-favorites/v1/favorites` - Add new favorite
- `PUT /wp-json/jazzedge-favorites/v1/favorites/{id}` - Update favorite
- `DELETE /wp-json/jazzedge-favorites/v1/favorites/{id}` - Delete favorite

### JavaScript Integration

The plugin provides global functions for other plugins to use:

```javascript
// Add a favorite
addJazzedgeFavorite(title, url, category, description)
  .then(response => console.log('Favorite added:', response))
  .catch(error => console.error('Error:', error));

// Get all favorites
getJazzedgeFavorites()
  .then(favorites => console.log('Favorites:', favorites))
  .catch(error => console.error('Error:', error));
```

## Database Schema

The plugin creates a `wp_jf_favorites` table with the following structure:

- `id` - Unique identifier
- `user_id` - WordPress user ID
- `title` - Lesson title
- `url` - Lesson URL
- `category` - Lesson category
- `description` - Lesson description
- `is_active` - Soft delete flag
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## Integration with Practice Hub

The Jazzedge Practice Hub can access favorites through the REST API:

```javascript
// In Practice Hub
fetch('/wp-json/jazzedge-favorites/v1/favorites')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Use data.favorites array
    }
  });
```

## Styling

The plugin includes CSS classes for customization:

- `.jf-favorites-btn` - Main button
- `.jf-favorites-container` - Container wrapper
- `.jf-favorites-message` - Success/error messages
- `.jf-favorites-added` - Added state styling

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Jazzedge Practice Hub (for integration)

## Changelog

### 1.0.0
- Initial release
- Shortcode support
- REST API endpoints
- Practice Hub integration
- Responsive design

## Support

For support and feature requests, contact the Jazzedge development team.
