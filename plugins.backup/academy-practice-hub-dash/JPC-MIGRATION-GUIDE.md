# JPC Data Migration Guide

This guide explains how to migrate JPC (JazzEdge Practice Curriculum™) data from the old system to the new Academy Practice Hub system.

## Overview

The migration process transfers data from the old JPC tables to the new Academy Practice Hub tables while maintaining data integrity and relationships.

### Old Tables (Source)
- `je_practice_curriculum` - Curriculum focus definitions
- `je_practice_curriculum_steps` - Step definitions for each curriculum
- `je_practice_curriculum_assignments` - User assignments
- `jpc_student_progress` - User progress tracking
- `je_practice_milestone_submissions` - Milestone video submissions

### New Tables (Destination)
- `jph_jpc_curriculum` - Curriculum focus definitions
- `jph_jpc_steps` - Step definitions for each curriculum
- `jph_jpc_user_assignments` - User assignments
- `jph_jpc_user_progress` - User progress tracking
- `jph_jpc_milestone_submissions` - Milestone video submissions
- `jph_practice_items` - Practice items for users (JPC auto-created)

## Migration Methods

### 1. Admin Interface (Recommended)

**Access:** WordPress Admin → Academy Practice Hub → JPC Migration

**Features:**
- Prerequisites validation
- Dry run capability
- Real-time progress tracking
- Error reporting
- User-friendly interface

**Steps:**
1. Navigate to the JPC Migration page
2. Review prerequisites validation
3. Choose migration options (dry run recommended first)
4. Click "Start Migration"
5. Review results and errors

### 2. REST API

**Endpoint:** `POST /wp-json/aph/v1/jpc/migrate`

**Parameters:**
- `dry_run` (string): "true" or "false" - Analyze without changes
- `batch_size` (integer): Records per batch (default: 100)
- `skip_existing` (string): "true" or "false" - Skip existing records
- `include_milestones` (string): "true" or "false" - Include milestone submissions
- `log_level` (string): Logging level (debug, info, warning, error)

**Example:**
```bash
curl -X POST "https://yoursite.com/wp-json/aph/v1/jpc/migrate" \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -d '{
    "dry_run": "true",
    "batch_size": 100,
    "skip_existing": "true",
    "include_milestones": "true"
  }'
```

### 3. Command Line Interface

**File:** `jpc-migration-cli.php`

**Usage:**
```bash
# Dry run (recommended first)
php jpc-migration-cli.php --dry-run

# Live migration
php jpc-migration-cli.php

# Custom options
php jpc-migration-cli.php --batch-size=50 --no-skip-existing

# Help
php jpc-migration-cli.php --help
```

**Options:**
- `--dry-run`: Analyze without making changes
- `--batch-size=N`: Number of records per batch
- `--no-skip-existing`: Don't skip existing records
- `--no-milestones`: Don't include milestone submissions
- `--help, -h`: Show help message

## Migration Process

### Step 1: Prerequisites Validation
- Checks if all required new tables exist
- Verifies at least one old table exists
- Reports any missing dependencies

### Step 2: Curriculum Data Migration
- Migrates `je_practice_curriculum` → `jph_jpc_curriculum`
- Preserves all focus definitions and metadata
- Uses `REPLACE` to handle duplicates

### Step 3: Steps Data Migration
- Migrates `je_practice_curriculum_steps` → `jph_jpc_steps`
- Preserves step relationships and video IDs
- Maintains key signature mappings

### Step 4: User Assignments Migration
- Migrates `je_practice_curriculum_assignments` → `jph_jpc_user_assignments`
- Only migrates non-deleted assignments
- Takes latest assignment per user
- Determines current step from assignment data

### Step 5: User Progress Migration
- Migrates `jpc_student_progress` → `jph_jpc_user_progress`
- Preserves all 12 key completion states
- Maintains user-curriculum relationships

### Step 6: Milestone Submissions Migration (Optional)
- Migrates `je_practice_milestone_submissions` → `jph_jpc_milestone_submissions`
- Preserves video URLs and grading data
- Maintains submission history

### Step 7: Practice Items Creation
- Creates JPC practice items for users with progress
- Sets up "JazzEdge Practice Curriculum™" as practice item
- Enables JPC in the new practice hub interface

## Safety Features

### Dry Run Mode
- Analyzes all data without making changes
- Provides detailed statistics
- Identifies potential issues
- **Always run dry run first!**

### Skip Existing Records
- Prevents overwriting existing data
- Safe for re-running migration
- Maintains data integrity

### Batch Processing
- Processes records in configurable batches
- Prevents memory issues with large datasets
- Allows for interruption and resumption

### Error Handling
- Comprehensive error logging
- Continues processing despite individual failures
- Detailed error reporting
- Rollback capability (manual)

## Best Practices

### Before Migration
1. **Backup your database** - Always create a full backup
2. **Run dry run first** - Analyze data without changes
3. **Test on staging** - Verify migration on test environment
4. **Check prerequisites** - Ensure all tables exist

### During Migration
1. **Monitor progress** - Watch for errors and warnings
2. **Don't interrupt** - Let the process complete
3. **Check logs** - Review error logs for issues
4. **Verify data** - Spot-check migrated data

### After Migration
1. **Test functionality** - Verify JPC works in new system
2. **Check user data** - Ensure progress is preserved
3. **Monitor performance** - Watch for any issues
4. **Keep old tables** - Don't delete until confirmed working

## Troubleshooting

### Common Issues

**"Required table does not exist"**
- Solution: Ensure Academy Practice Hub is properly activated
- Check: `wp jph_tables` exist in database

**"No old JPC tables found"**
- Solution: Verify old JPC tables exist
- Check: `je_practice_curriculum`, `jpc_student_progress` tables

**"Migration failed with errors"**
- Solution: Review error logs for specific issues
- Check: Database permissions, table constraints

**"Partial migration completed"**
- Solution: Re-run with `skip_existing=true`
- Check: Individual error messages for failed records

### Data Validation

After migration, verify:
- Curriculum items match between old and new tables
- User progress is preserved correctly
- Assignments are properly migrated
- Milestone submissions are intact
- Practice items are created for users

### Rollback

If issues occur:
1. **Don't delete old tables** - Keep as backup
2. **Clear new tables** - Use JPC Reset functionality
3. **Fix issues** - Address any problems
4. **Re-run migration** - Start fresh

## Support

For issues or questions:
1. Check error logs in WordPress debug log
2. Review migration statistics and results
3. Test with dry run mode first
4. Contact support with specific error messages

## Technical Details

### Database Schema Compatibility
The new tables are designed to be compatible with the old schema while adding new features:
- Added `created_at` and `updated_at` timestamps
- Enhanced indexing for performance
- Better data types and constraints
- Foreign key relationships

### Performance Considerations
- Batch processing prevents memory issues
- Indexes optimize query performance
- `REPLACE` statements handle duplicates efficiently
- Minimal data transformation reduces overhead

### Security
- Admin-only access to migration tools
- Nonce verification for API calls
- Input validation and sanitization
- Error logging without sensitive data exposure
