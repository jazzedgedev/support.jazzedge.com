# Academy Lesson Manager Plugin

A WordPress plugin for managing Academy courses, lessons, and chapters with comprehensive CRUD capabilities.

## Overview

The Academy Lesson Manager plugin provides a clean, modern interface for managing educational content within WordPress. It uses custom database tables to store course, lesson, and chapter data, providing better performance and data integrity than traditional WordPress posts.

## Features

### Phase 1 (Current)
- **Courses Management**: View and edit course information
- **Lessons Management**: View and edit lesson details with resources
- **Chapters Management**: View chapter information and video sources
- **Search & Sort**: Full-text search and column sorting
- **Data Integrity**: Read-only interface to validate existing data
- **Responsive Design**: Mobile-friendly admin interface

### Future Phases
- Create/Update/Delete functionality
- Bulk operations
- Data import/export
- AI-powered search
- Advanced filtering
- Analytics and reporting

## Installation

1. Upload the plugin files to `/wp-content/plugins/academy-lesson-manager/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create the required database tables

## Database Schema

The plugin creates three custom tables with the `wp_alm_` prefix:

### wp_alm_courses
- `ID` (Primary Key)
- `post_id` (WordPress post reference)
- `course_title` (Course name)
- `course_description` (Course description)
- `site` (Site identifier: JE, JPD, SPJ, etc.)
- `created_at`, `updated_at` (Timestamps)

### wp_alm_lessons
- `ID` (Primary Key)
- `post_id` (WordPress post reference)
- `course_id` (Foreign key to courses)
- `lesson_title` (Lesson name)
- `lesson_description` (Lesson description)
- `post_date` (Release date)
- `sku` (Product SKU)
- `site` (Site identifier)
- `duration` (Duration in seconds)
- `vtt` (Subtitle file reference)
- `resources` (Serialized resources array)
- `success_lesson` (Success lesson flag)
- `success_style` (Success lesson style)
- `success_order` (Success lesson order)
- `song_lesson` (Song lesson flag)
- `jami_done` (Completion status)
- `slug` (URL slug)
- `created_at`, `updated_at` (Timestamps)

### wp_alm_chapters
- `ID` (Primary Key)
- `lesson_id` (Foreign key to lessons)
- `chapter_title` (Chapter name)
- `menu_order` (Display order)
- `vimeo_id` (Vimeo video ID)
- `bunny_url` (Bunny CDN URL)
- `youtube_id` (YouTube video ID)
- `duration` (Duration in seconds)
- `free` (Free chapter flag)
- `slug` (URL slug)
- `post_date` (Release date)
- `created_at`, `updated_at` (Timestamps)

## Data Migration

**Important**: The plugin creates empty tables. To migrate data from existing `academy_*` tables, you'll need to run a separate migration script.

### Migration Steps

1. **Backup your database** before running any migration
2. **Test the migration** on a staging environment first
3. **Run the migration script** (to be provided separately)
4. **Verify data integrity** using the plugin's admin interface

### Sample Migration Query

```sql
-- Migrate courses
INSERT INTO wp_alm_courses (ID, post_id, course_title, course_description, site, created_at, updated_at)
SELECT ID, post_id, course_title, course_description, site, NOW(), NOW()
FROM academy_courses;

-- Migrate lessons
INSERT INTO wp_alm_lessons (ID, post_id, course_id, lesson_title, lesson_description, post_date, sku, site, duration, vtt, resources, success_lesson, success_style, success_order, song_lesson, jami_done, slug, created_at, updated_at)
SELECT ID, post_id, course_id, lesson_title, lesson_description, post_date, sku, site, duration, vtt, resources, success_lesson, success_style, success_order, song_lesson, jami_done, slug, NOW(), NOW()
FROM academy_lessons;

-- Migrate chapters
INSERT INTO wp_alm_chapters (ID, lesson_id, chapter_title, menu_order, vimeo_id, bunny_url, youtube_id, duration, free, slug, post_date, created_at, updated_at)
SELECT ID, lesson_id, chapter_title, menu_order, vimeo_id, bunny_url, youtube_id, duration, free, slug, post_date, NOW(), NOW()
FROM academy_chapters;
```

## Usage

### Accessing the Plugin

1. Navigate to **Academy Manager** in the WordPress admin menu
2. Choose from three submenus:
   - **Courses**: Manage course information
   - **Lessons**: Manage lesson details and resources
   - **Chapters**: View chapter information

### Course Management

- **List View**: Browse all courses with sorting and search
- **Edit View**: View detailed course information
- **Course Lessons**: See all lessons belonging to a course

### Lesson Management

- **List View**: Browse lessons with advanced filtering
- **Edit View**: View comprehensive lesson details
- **Resources**: Display lesson resources (PDFs, audio files, etc.)
- **Chapters**: Show all chapters within a lesson

### Chapter Management

- **List View**: Browse chapters with video source information
- **Edit View**: View detailed chapter information
- **Video Sources**: Links to Vimeo and YouTube videos

## Site Identifiers

The plugin supports multiple site identifiers:

- `JE` - JazzEdge
- `JPD` - Jazz Piano Department
- `SPJ` - Smooth Piano Jazz
- `JCM` - Jazz Chord Mastery
- `TTM` - The Theory Master
- `CPL` - Contemporary Piano Lessons
- `FPL` - Free Piano Lessons
- `RPL` - Rock Piano Lessons
- `PBP` - Piano By Pattern
- `JPT` - Jazz Piano Techniques
- `MTO` - Music Theory Online

## Success Lesson Styles

- `basics` - Basics
- `rock` - Rock
- `standards` - Standards
- `improvisation` - Improvisation
- `blues` - Blues

## Skill Levels

- `N/A` - Not Applicable
- `Beginner` - Beginner
- `Intermediate` - Intermediate
- `Advanced` - Advanced
- `Professional` - Professional

## Technical Details

### File Structure

```
plugins/academy-lesson-manager/
├── academy-lesson-manager.php     # Main plugin file
├── includes/
│   ├── class-database.php         # Database management
│   ├── class-helpers.php          # Utility functions
│   ├── class-admin-courses.php    # Courses admin interface
│   ├── class-admin-lessons.php    # Lessons admin interface
│   └── class-admin-chapters.php   # Chapters admin interface
├── assets/
│   ├── css/
│   │   └── admin.css              # Admin styling
│   └── js/
│       └── admin.js               # Admin JavaScript
└── README.md                      # This file
```

### Hooks and Filters

The plugin provides several hooks for customization:

- `alm_before_course_display` - Before course details display
- `alm_after_course_display` - After course details display
- `alm_before_lesson_display` - Before lesson details display
- `alm_after_lesson_display` - After lesson details display
- `alm_before_chapter_display` - Before chapter details display
- `alm_after_chapter_display` - After chapter details display

### Database Indexes

The plugin creates optimized indexes for:
- Foreign key relationships
- Search fields (titles, descriptions)
- Sort fields (dates, order)
- Status fields (flags)

## Troubleshooting

### Common Issues

1. **Tables not created**: Check WordPress database permissions
2. **Data not displaying**: Verify migration completed successfully
3. **Search not working**: Check database collation settings
4. **Performance issues**: Ensure proper indexes are created

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Database Verification

Check if tables exist:

```sql
SHOW TABLES LIKE 'wp_alm_%';
```

Check table structure:

```sql
DESCRIBE wp_alm_courses;
DESCRIBE wp_alm_lessons;
DESCRIBE wp_alm_chapters;
```

## Support

For support and feature requests, please contact the development team.

## Changelog

### Version 1.0.0
- Initial release
- Course, lesson, and chapter management
- Search and sorting functionality
- Read-only interface for data validation
- Responsive admin design

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed for JazzEdge Academy by the development team.
