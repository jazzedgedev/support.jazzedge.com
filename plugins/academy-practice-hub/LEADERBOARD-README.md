# Academy Practice Hub - Leaderboard System

## Overview

The leaderboard system allows users to compete and compare their practice progress with other students. Users can set custom display names and control their visibility on the leaderboard.

## Features

### Database Schema
- **display_name**: Custom name for leaderboard display (VARCHAR 100, nullable)
- **show_on_leaderboard**: Boolean flag to control visibility (TINYINT 1, default 1)
- Indexes optimized for leaderboard queries

### REST API Endpoints

#### GET `/wp-json/aph/v1/leaderboard`
Retrieve leaderboard data with pagination and sorting.

**Parameters:**
- `limit` (int, default: 50): Number of users per page (max 100)
- `offset` (int, default: 0): Pagination offset
- `sort_by` (string, default: 'total_xp'): Sort field

**Sort Options:**
- `total_xp` - Total experience points
- `current_level` - Current user level
- `current_streak` - Current practice streak
- `total_sessions` - Total practice sessions
- `total_minutes` - Total practice minutes

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "user_id": 123,
      "position": 1,
      "total_xp": 5000,
      "current_level": 15,
      "current_streak": 30,
      "total_sessions": 100,
      "total_minutes": 2500,
      "badges_earned": 8,
      "leaderboard_name": "PianoMaster2024"
    }
  ],
  "pagination": {
    "limit": 50,
    "offset": 0,
    "sort_by": "total_xp"
  }
}
```

#### GET `/wp-json/aph/v1/leaderboard/position`
Get current user's leaderboard position.

**Parameters:**
- `sort_by` (string, default: 'total_xp'): Sort field

**Response:**
```json
{
  "success": true,
  "data": {
    "user_id": 123,
    "position": 5,
    "sort_by": "total_xp"
  }
}
```

#### GET `/wp-json/aph/v1/leaderboard/stats`
Get leaderboard statistics summary.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_users": 150,
    "leaderboard_users": 120,
    "avg_xp": 2500,
    "max_xp": 10000,
    "avg_level": 8,
    "max_level": 20,
    "avg_streak": 15,
    "max_streak": 100
  }
}
```

#### POST `/wp-json/aph/v1/leaderboard/display-name`
Update user's display name for leaderboard.

**Body:**
```json
{
  "display_name": "PianoMaster2024"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Display name updated successfully",
  "data": {
    "user_id": 123,
    "display_name": "PianoMaster2024"
  }
}
```

#### POST `/wp-json/aph/v1/leaderboard/visibility`
Update user's leaderboard visibility (ready for future use).

**Body:**
```json
{
  "show_on_leaderboard": true
}
```

### Frontend Components

#### Shortcode: `[jph_leaderboard]`

**Attributes:**
- `limit` (int, default: 50): Number of users to display
- `sort_by` (string, default: 'total_xp'): Default sort field
- `show_user_position` (string, default: 'true'): Show current user's position
- `show_stats` (string, default: 'true'): Show leaderboard statistics

**Example:**
```
[jph_leaderboard limit="25" sort_by="current_streak" show_user_position="true" show_stats="true"]
```

#### Dashboard Integration
- "Leaderboard Name" button in dashboard header
- Modal dialog for setting display name
- Real-time updates via AJAX

### Database Methods

#### JPH_Database Class

**New Methods:**
- `get_leaderboard($limit, $offset, $sort_by)` - Retrieve leaderboard data
- `get_user_leaderboard_position($user_id, $sort_by)` - Get user's position
- `update_user_display_name($user_id, $display_name)` - Update display name
- `update_user_leaderboard_visibility($user_id, $show_on_leaderboard)` - Update visibility
- `get_leaderboard_stats()` - Get statistics summary

### Security Features

- Input validation and sanitization
- SQL injection protection via prepared statements
- User permission checks for authenticated endpoints
- Rate limiting considerations (can be added)

### Performance Optimizations

- Database indexes on `show_on_leaderboard` and `total_xp`
- Composite index for leaderboard sorting
- Pagination to limit data transfer
- Efficient queries with JOINs

### Privacy Considerations

- Users can set custom display names
- `show_on_leaderboard` field ready for privacy controls
- Display name fallback to WordPress display name or username
- No sensitive user data exposed

## Installation & Setup

1. **Database Schema**: Automatically created on plugin activation
2. **REST API**: Endpoints registered automatically
3. **Frontend**: Shortcode available immediately

## Usage Examples

### Basic Leaderboard
```
[jph_leaderboard]
```

### Top 10 by Streak
```
[jph_leaderboard limit="10" sort_by="current_streak"]
```

### Minimal Leaderboard
```
[jph_leaderboard show_user_position="false" show_stats="false"]
```

## Future Enhancements

### Ready for Implementation
- User privacy controls (hide from leaderboard)
- Admin moderation tools
- Leaderboard categories (by level, instrument, etc.)
- Achievement highlights
- Social features (follow users, challenges)

### Potential Features
- Time-based leaderboards (weekly, monthly)
- Team competitions
- Leaderboard rewards
- Export functionality
- Advanced filtering

## Technical Notes

### Database Migration
The system automatically adds leaderboard columns to existing `jph_user_stats` tables:
- `display_name` VARCHAR(100) NULL
- `show_on_leaderboard` TINYINT(1) DEFAULT 1

### Compatibility
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+
- jQuery (for frontend interactions)

### Dependencies
- WordPress REST API
- jQuery (enqueued by WordPress)
- Academy Practice Hub core functionality

## Troubleshooting

### Common Issues

1. **Leaderboard not loading**: Check REST API permissions and database connection
2. **Display name not saving**: Verify user authentication and API endpoint
3. **Performance issues**: Check database indexes and consider pagination limits

### Debug Mode
Enable WordPress debug mode to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For technical support or feature requests, please refer to the main Academy Practice Hub documentation or contact the development team.
