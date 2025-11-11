# Academy Practice Hub - Widgets Documentation

## Practice Stats Widget

The Practice Stats Widget displays user practice statistics in a customizable format.

### Shortcode Usage

```php
[jph_stats_widget]
```

### Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `user_id` | `current` | User ID to display stats for (`current` for logged-in user or specific user ID) |
| `show` | `xp,level,streak,badges` | Comma-separated list of stats to display |
| `style` | `compact` | Widget style (`compact` or `detailed`) |
| `title` | `Practice Stats` | Widget title |
| `show_title` | `true` | Show/hide widget title |
| `cache` | `true` | Enable caching (future feature) |

### Available Stats

- `xp` - Total XP earned
- `level` - Current level
- `streak` - Current practice streak
- `badges` - Number of badges earned
- `sessions` - Total practice sessions
- `minutes` - Total practice minutes
- `gems` - Gem balance
- `hearts` - Hearts count

### Examples

#### Basic Usage
```php
[jph_stats_widget]
```

#### Custom Stats Selection
```php
[jph_stats_widget show="xp,level,streak"]
```

#### Detailed Style
```php
[jph_stats_widget style="detailed" title="My Progress"]
```

#### Specific User
```php
[jph_stats_widget user_id="123" title="Student Progress"]
```

#### Minimal Widget
```php
[jph_stats_widget show="xp,level" show_title="false"]
```

### Styling

The widget includes responsive CSS that adapts to different screen sizes:

- **Desktop**: Auto-fit grid layout
- **Tablet**: 2-column grid
- **Mobile**: 2-column grid

### Customization

You can customize the widget appearance by adding CSS to your theme:

```css
.jph-stats-widget {
    /* Custom widget container styles */
}

.jph-stat-item {
    /* Custom stat item styles */
}

.jph-stat-value {
    /* Custom value styles */
}

.jph-stat-label {
    /* Custom label styles */
}
```

### Security

- All output is sanitized to prevent XSS attacks
- User permissions are checked for non-current users
- Input validation ensures only valid user IDs and stats are displayed

### Performance

- Efficient database queries
- Responsive design with minimal CSS
- Future caching support planned

### Troubleshooting

**Widget not displaying:**
- Check if user is logged in (for `user_id="current"`)
- Verify user ID exists (for specific user IDs)
- Ensure valid stats are selected in `show` attribute

**Styling issues:**
- Check for CSS conflicts with theme
- Verify widget CSS is loading properly
- Test responsive behavior on different screen sizes

### Future Enhancements

- Caching support for better performance
- Animation effects for stat changes
- Custom color schemes
- Progress bars for goals
- Export functionality
