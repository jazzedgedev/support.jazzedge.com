# Academy Lesson Manager v2.0

A simplified WordPress plugin for migrating and managing JazzEdge Academy lesson content.

## Overview

This plugin provides a clean, focused solution for migrating lesson data from legacy academy database tables into WordPress-native `lesson` posts with ACF field integration.

## Features

### 🎯 **Core Functionality**
- **Simple Migration**: Migrates `academy_lessons`, `academy_courses`, `academy_chapters`, and `studio-event` posts
- **ACF Integration**: Uses existing ACF fields on `lesson` post type
- **Clean Admin Interface**: Single admin page for migration and management
- **Progress Tracking**: Basic user progress tracking in custom table

### 🔄 **Migration Process**
- **Phase 1**: Prepare course data for ACF mapping
- **Phase 2**: Migrate academy lessons to `lesson` posts
- **Phase 3**: Convert studio events to lessons
- **Phase 4**: Migrate chapters to lesson metadata

### 📊 **Admin Interface**
- **Status Cards**: Visual overview of migration progress
- **One-Click Migration**: Simple button to run complete migration
- **Real-time Logging**: Live migration log with copy functionality
- **Legacy Cleanup**: Remove old lessons after testing

## Installation

### Prerequisites
1. **WordPress 5.0+** with PHP 7.4+
2. **ACF Plugin** (Advanced Custom Fields)
3. **Existing `lesson` post type** (via MetaBox)
4. **Legacy Academy Tables** (`academy_lessons`, `academy_courses`, `academy_chapters`)

### Setup Steps
1. Upload plugin to `/wp-content/plugins/academy-lesson-manager/`
2. Activate plugin in WordPress admin
3. Ensure `lesson` post type exists via MetaBox
4. Ensure ACF is active and configured

## Usage

### Running Migration
1. Go to **Lesson Manager** in WordPress admin
2. Review the status cards showing current data
3. Click **Run Migration** to start the process
4. Monitor progress in the migration log
5. After testing, use **Cleanup Legacy Lessons** to remove old data

### Managing Lessons
- View migrated lessons at **Lesson Manager > Lessons**
- Edit lessons using standard WordPress editor
- ACF fields are automatically populated during migration

## File Structure

```
academy-lesson-manager/
├── academy-lesson-manager.php          # Main plugin file
├── includes/
│   ├── class-migration.php             # Migration logic
│   ├── class-admin.php                 # Admin interface
│   └── class-frontend.php              # Frontend display
└── assets/
    ├── css/
    │   ├── admin.css                   # Admin styles
    │   └── frontend.css                # Frontend styles
    └── js/
        ├── admin.js                    # Admin JavaScript
        └── frontend.js                 # Frontend JavaScript
```

## Database Changes

### New Table: `wp_ja_user_progress`
- Tracks user progress through lessons
- Stores completion percentage and status
- Links users to lesson posts

### Post Meta Fields
- `_lesson_source`: 'migrated' or 'legacy'
- `_lesson_legacy_id`: Original academy table ID
- `_lesson_legacy_course_id`: Original course ID
- `_lesson_legacy_type`: 'studio-event' for converted events
- `_lesson_chapters`: Serialized chapter data

## ACF Field Mapping

The migration automatically populates these ACF fields:
- `lesson_id`: Original academy lesson ID
- `lesson_required_membership`: Membership level (defaults to 'essential')
- `lesson_type`: 'song_lesson', 'success_lesson', or 'standard'
- `free_lesson`: Boolean for free content
- `lesson_course_title`: Course title from academy_courses
- `lesson_course_description`: Course description

## Migration Safety

- **No Data Loss**: Original tables remain untouched
- **Reversible**: Can identify and clean up migrated content
- **Incremental**: Can be run multiple times safely
- **Legacy Marking**: Existing lessons marked as 'legacy' for cleanup

## Support

For issues or questions:
1. Check the migration log for error messages
2. Verify all prerequisites are met
3. Ensure database tables exist and are accessible

## Version History

- **v2.0.0**: Complete rebuild with simplified architecture
- Focused on core migration functionality
- Clean admin interface
- ACF integration
- Basic progress tracking
