# Lesson Favorites Button - Usage Guide

## Overview
The lesson favorites button is a reusable component that allows users to save lesson URLs as favorites. It can be used anywhere on your website and automatically detects URL parameters to pre-fill the form.

## Basic Usage

### 1. Include the Component
```php
<?php
// Include the lesson favorites button component
require_once get_stylesheet_directory() . '/lesson-favorites-button.php';

// Display the button
show_lesson_favorites_button();
?>
```

### 2. Customize the Button
```php
<?php
show_lesson_favorites_button(array(
    'button_text' => 'Save This Lesson',
    'button_class' => 'my-custom-btn',
    'button_style' => 'background: #ff6b35; color: white;',
    'show_icon' => true,
    'icon' => '💾',
    'auto_detect' => true
));
?>
```

## URL Parameter Support

The button automatically detects URL parameters to pre-fill the form:

```
https://yoursite.com/lesson-page/?title=Major%20Scale%20Practice&url=https://example.com/lesson&category=technique&description=Learn%20major%20scales
```

### Supported Parameters:
- `title` - Lesson title
- `url` - Lesson URL
- `category` - Lesson category
- `description` - Lesson description

## Usage Examples

### 1. In a WordPress Post/Page
```php
<?php
// In your template file
if (is_user_logged_in()) {
    show_lesson_favorites_button(array(
        'button_text' => '⭐ Add to My Favorites',
        'button_class' => 'lesson-fav-btn'
    ));
}
?>
```

### 2. In a Custom Post Type Template
```php
<?php
// In single-lesson.php or similar
get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();
        ?>
        <article>
            <h1><?php the_title(); ?></h1>
            <div class="lesson-content">
                <?php the_content(); ?>
            </div>
            
            <?php if (is_user_logged_in()): ?>
                <div class="lesson-actions">
                    <?php
                    show_lesson_favorites_button(array(
                        'button_text' => 'Save This Lesson',
                        'button_class' => 'save-lesson-btn'
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </article>
        <?php
    }
}

get_footer();
?>
```

### 3. In a Widget
```php
<?php
// In your widget class
class Lesson_Favorites_Widget extends WP_Widget {
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (is_user_logged_in()) {
            show_lesson_favorites_button(array(
                'button_text' => 'Quick Save',
                'button_class' => 'widget-fav-btn'
            ));
        }
        
        echo $args['after_widget'];
    }
}
?>
```

### 4. In a Shortcode
```php
<?php
// Add this to your functions.php
function lesson_favorites_shortcode($atts) {
    $atts = shortcode_atts(array(
        'text' => '⭐ Add Lesson Favorite',
        'class' => 'lesson-favorites-btn'
    ), $atts);
    
    if (!is_user_logged_in()) {
        return '<p>Please log in to save lesson favorites.</p>';
    }
    
    ob_start();
    show_lesson_favorites_button(array(
        'button_text' => $atts['text'],
        'button_class' => $atts['class']
    ));
    return ob_get_clean();
}
add_shortcode('lesson_favorites', 'lesson_favorites_shortcode');
?>

<!-- Usage in content: -->
[lesson_favorites text="Save This Lesson" class="my-custom-class"]
```

## Admin Area Features

### Accessing the Admin Area
1. Go to **WordPress Admin** → **Practice Hub** → **Lesson Favorites**
2. View all lesson favorites from all users
3. Filter by user, category, or search
4. Export favorites to CSV
5. Delete favorites if needed

### Admin Features:
- **Statistics Dashboard**: Total favorites, active users, popular categories
- **User Management**: See which users have saved favorites
- **Filtering**: Filter by user, category, or search text
- **Export**: Download all favorites as CSV
- **Bulk Actions**: Delete multiple favorites

## Integration with Practice Hub

### 1. Practice Dashboard Integration
The lesson favorites are automatically integrated into the practice dashboard:
- Users can add favorites from the dashboard
- Favorites appear in the "Add Practice Item" modal
- Users can quickly create practice items from their favorites

### 2. REST API Endpoints
The system provides REST API endpoints for programmatic access:

```javascript
// Get all favorites for current user
fetch('/wp-json/jph/v1/lesson-favorites', {
    headers: {
        'X-WP-Nonce': wpApiSettings.nonce
    }
})

// Add a new favorite
fetch('/wp-json/jph/v1/lesson-favorites', {
    method: 'POST',
    headers: {
        'X-WP-Nonce': wpApiSettings.nonce,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        title: 'Lesson Title',
        url: 'https://example.com/lesson',
        category: 'technique',
        description: 'Optional description'
    })
})
```

## Customization Options

### CSS Customization
The button uses CSS classes that you can customize:

```css
/* Custom button styling */
.my-custom-fav-btn {
    background: linear-gradient(45deg, #ff6b35, #f7931e);
    border: none;
    border-radius: 25px;
    padding: 15px 30px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.my-custom-fav-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
}
```

### JavaScript Customization
You can add custom JavaScript to enhance the functionality:

```javascript
// Custom event listener for when a favorite is added
document.addEventListener('lessonFavoriteAdded', function(e) {
    console.log('Favorite added:', e.detail);
    // Your custom code here
});

// Custom event listener for when a favorite is deleted
document.addEventListener('lessonFavoriteDeleted', function(e) {
    console.log('Favorite deleted:', e.detail);
    // Your custom code here
});
```

## Best Practices

### 1. User Experience
- Always check if user is logged in before showing the button
- Provide clear feedback when favorites are added/removed
- Use consistent styling across your site

### 2. Performance
- The component only loads its assets once per page
- Use the `auto_detect` parameter wisely (set to false if not needed)
- Consider lazy loading for pages with many buttons

### 3. Security
- All data is sanitized and validated
- Users can only manage their own favorites
- Admin users can view all favorites but should be careful with deletion

## Troubleshooting

### Common Issues:

1. **Button not showing**: Check if user is logged in
2. **Modal not opening**: Check for JavaScript errors in console
3. **Form not submitting**: Check REST API nonce and permissions
4. **Styling issues**: Check for CSS conflicts with your theme

### Debug Mode:
Add this to your wp-config.php for debugging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For issues or questions:
1. Check the browser console for JavaScript errors
2. Check the WordPress error log
3. Verify REST API endpoints are working
4. Test with a default WordPress theme to rule out conflicts
