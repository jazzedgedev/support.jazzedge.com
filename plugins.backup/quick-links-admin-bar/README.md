# Quick Links Admin Bar

A simple WordPress plugin that adds customizable quick links to the WordPress admin bar for easy access to frequently used pages and tools.

## Features

- **Admin Bar Integration**: Adds a "Quick Links" menu to the right side of the WordPress admin bar
- **Easy Management**: Simple admin interface to add, edit, delete, and reorder links
- **Drag & Drop**: Reorder links by dragging them up or down
- **Inline Editing**: Edit links directly in the admin interface without page reloads
- **Custom Styling**: Orange/red (#f04e23) background with white text to match your brand
- **Admin Only**: Only administrators can manage and see the quick links
- **New Tab Links**: All links open in new tabs for convenience

## Installation

1. Upload the `quick-links-admin-bar` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Quick Links** in your WordPress admin menu to start adding links

## Usage

### Adding Quick Links

1. Navigate to **Quick Links** in your WordPress admin menu
2. Fill in the "Title" and "URL" fields
3. Click "Add Quick Link"
4. The link will immediately appear in your admin bar

### Managing Quick Links

- **Edit**: Click the "Edit" button to modify a link's title or URL
- **Delete**: Click the "Delete" button to remove a link
- **Reorder**: Drag the handle (⋮⋮) to reorder links
- **View**: All links appear in the admin bar dropdown menu

### Admin Bar Location

The Quick Links menu appears on the right side of the WordPress admin bar, alongside other user-specific items like "Search Contacts" and "Ticket Summary".

## Customization

The plugin uses the color #f04e23 (orange/red) for the main menu item. You can customize this by modifying the CSS in `/assets/css/admin.css`:

```css
#wp-admin-bar-qlab-quick-links .ab-item {
    background-color: #your-color !important;
    color: white !important;
}
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Administrator privileges to manage links

## Support

This plugin is developed by Katahdin AI. For support or feature requests, please visit [katahdin.ai](https://katahdin.ai).

## License

GPL v2 or later
