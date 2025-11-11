# Jazzedge Docs Plugin

A comprehensive documentation and knowledge base system for Jazzedge Academy support articles. This plugin replaces BetterDocs and provides a complete solution for managing and displaying documentation on your WordPress site.

## Features

- **Custom Post Type**: Dedicated `jazzedge_doc` post type for documentation articles
- **Hierarchical Categories**: Support for parent/child category relationships
- **Live Search**: Real-time search functionality with AJAX
- **Ratings & Feedback**: User ratings and feedback system
- **Analytics**: Track views and popular articles
- **Table of Contents**: Automatic table of contents generation
- **Related Docs**: Manual association of related articles
- **Shortcodes**: Easy-to-use shortcodes for displaying content
- **REST API**: Full REST API support for integrations
- **Print-Friendly**: Print-optimized styles
- **Breadcrumbs**: Navigation breadcrumbs for better UX

## Installation

1. Upload the `jazzedge-docs` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create necessary database tables on activation

## Usage

### Creating Docs

1. Navigate to **Docs > Add New** in the WordPress admin
2. Enter your doc title and content
3. Assign categories (supports hierarchical categories)
4. Configure settings:
   - Show/hide table of contents
   - Mark as featured doc
5. Add related docs in the "Related Docs" meta box
6. Publish your doc

### Managing Categories

1. Navigate to **Docs > Categories**
2. Create categories with optional:
   - Parent category (for hierarchy)
   - Order number (lower numbers appear first)
   - Icon (WordPress dashicon class)
   - Description

### Shortcodes

#### Search Bar
```
[jazzedge_docs_search placeholder="Search documentation..." show_results="yes" results_count="10"]
```

#### Doc List
```
[jazzedge_docs_list category="category-slug" limit="12" orderby="date" order="DESC" layout="grid" columns="3"]
```

Parameters:
- `category` - Category slug to filter by
- `category_id` - Category ID to filter by
- `limit` - Number of docs to display (-1 for all)
- `orderby` - Order by field (date, title, etc.)
- `order` - ASC or DESC
- `featured` - Show only featured docs (yes/no)
- `layout` - grid or list
- `columns` - Number of columns for grid layout

#### Categories Grid
```
[jazzedge_docs_categories parent="" layout="grid" columns="3"]
```

Parameters:
- `parent` - Parent category ID (empty for top-level)
- `layout` - grid or list
- `columns` - Number of columns for grid layout

#### Single Doc
```
[jazzedge_doc_single id="123"]
```
or
```
[jazzedge_doc_single slug="doc-slug"]
```

### Settings

Navigate to **Docs > Settings** to configure:
- Archive page title and description
- Search placeholder text
- Docs per page

### Analytics

Navigate to **Docs > Analytics** to view:
- Popular docs by view count
- Recent views

## Database Tables

The plugin creates the following tables:
- `wp_jazzedge_docs_ratings` - Stores user ratings and feedback
- `wp_jazzedge_docs_analytics` - Tracks doc views
- `wp_jazzedge_docs_related` - Stores related doc associations

## Styling

The plugin uses CSS variables matching Jazzedge Academy design system:
- Primary: `#004555`
- Secondary: `#239B90`
- Accent: `#F04E23`

All styles are in `assets/css/frontend.css` and can be customized.

## REST API Endpoints

- `GET /wp-json/jazzedge-docs/v1/search?q=query&limit=10`
- `POST /wp-json/jazzedge-docs/v1/rating` (doc_id, rating, feedback)
- `GET /wp-json/jazzedge-docs/v1/docs?category=slug&limit=10&offset=0`
- `GET /wp-json/jazzedge-docs/v1/categories?parent=0`

## Template Files

The plugin includes template files that can be overridden in your theme:
- `templates/archive-doc.php` - Archive page template
- `templates/single-doc.php` - Single doc template

To override, create these files in your theme:
- `archive-jazzedge_doc.php`
- `single-jazzedge_doc.php`

## Support

For support and questions, contact the JazzEdge development team.

## Version

1.0.0

