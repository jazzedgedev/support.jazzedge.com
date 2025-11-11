<?php
/**
 * JPC Migration Command Line Script
 * 
 * Simple command-line interface for testing JPC migration
 * Usage: php jpc-migration-cli.php [--dry-run] [--batch-size=100]
 * 
 * @package Academy_Practice_Hub
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

// Include migration class
require_once dirname(__FILE__) . '/includes/class-jpc-migration.php';

// Parse command line arguments
$options = array(
    'dry_run' => false,
    'batch_size' => 100,
    'skip_existing' => true,
    'include_milestones' => true,
    'log_level' => 'info'
);

foreach ($argv as $arg) {
    if ($arg === '--dry-run') {
        $options['dry_run'] = true;
    } elseif (strpos($arg, '--batch-size=') === 0) {
        $options['batch_size'] = intval(substr($arg, 13));
    } elseif ($arg === '--no-skip-existing') {
        $options['skip_existing'] = false;
    } elseif ($arg === '--no-milestones') {
        $options['include_milestones'] = false;
    } elseif ($arg === '--help' || $arg === '-h') {
        echo "JPC Migration CLI Tool\n";
        echo "Usage: php jpc-migration-cli.php [options]\n\n";
        echo "Options:\n";
        echo "  --dry-run              Analyze data without making changes\n";
        echo "  --batch-size=N         Number of records per batch (default: 100)\n";
        echo "  --no-skip-existing     Don't skip existing records\n";
        echo "  --no-milestones        Don't include milestone submissions\n";
        echo "  --help, -h             Show this help message\n\n";
        echo "Examples:\n";
        echo "  php jpc-migration-cli.php --dry-run\n";
        echo "  php jpc-migration-cli.php --batch-size=50\n";
        exit(0);
    }
}

echo "JPC Migration CLI Tool\n";
echo "=====================\n\n";

// Validate prerequisites
echo "Validating prerequisites...\n";
$validation = JPH_JPC_Migration::validate_prerequisites();

if (!$validation['valid']) {
    echo "❌ Prerequisites not met:\n";
    foreach ($validation['issues'] as $issue) {
        echo "   - $issue\n";
    }
    exit(1);
}

echo "✅ Prerequisites validated successfully!\n";
echo "   Found {$validation['old_tables_found']} old JPC tables to migrate from.\n\n";

// Show migration options
echo "Migration Options:\n";
echo "   Dry Run: " . ($options['dry_run'] ? 'Yes' : 'No') . "\n";
echo "   Batch Size: {$options['batch_size']}\n";
echo "   Skip Existing: " . ($options['skip_existing'] ? 'Yes' : 'No') . "\n";
echo "   Include Milestones: " . ($options['include_milestones'] ? 'Yes' : 'No') . "\n\n";

if ($options['dry_run']) {
    echo "🔍 Starting dry run analysis...\n";
} else {
    echo "⚠️  Starting LIVE migration...\n";
    echo "   This will modify your database!\n\n";
    
    // Ask for confirmation
    echo "Are you sure you want to proceed? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 'y') {
        echo "Migration cancelled.\n";
        exit(0);
    }
}

echo "\nStarting migration...\n";
echo str_repeat('-', 50) . "\n";

// Perform migration
$start_time = microtime(true);
$result = JPH_JPC_Migration::migrate_all_jpc_data($options['dry_run'], $options);
$end_time = microtime(true);

echo "\n" . str_repeat('-', 50) . "\n";

if ($result['success']) {
    echo "✅ Migration completed successfully!\n\n";
    
    echo "Migration Statistics:\n";
    echo "   Curriculum Items: " . ($result['stats']['curriculum_items'] ?? 0) . "\n";
    echo "   Steps: " . ($result['stats']['steps'] ?? 0) . "\n";
    echo "   User Assignments: " . ($result['stats']['user_assignments'] ?? 0) . "\n";
    echo "   User Progress Records: " . ($result['stats']['user_progress'] ?? 0) . "\n";
    echo "   Milestone Submissions: " . ($result['stats']['milestone_submissions'] ?? 0) . "\n";
    
    if (!empty($result['stats']['errors'])) {
        echo "\n⚠️  Errors encountered:\n";
        foreach ($result['stats']['errors'] as $error) {
            echo "   - $error\n";
        }
    }
    
    $duration = round($end_time - $start_time, 2);
    echo "\nMigration completed in {$duration} seconds.\n";
    
    if ($options['dry_run']) {
        echo "\n💡 This was a dry run. No data was actually migrated.\n";
        echo "   Run without --dry-run to perform the actual migration.\n";
    }
    
} else {
    echo "❌ Migration failed!\n";
    echo "Error: " . $result['error'] . "\n";
    
    if (!empty($result['stats']['errors'])) {
        echo "\nAdditional errors:\n";
        foreach ($result['stats']['errors'] as $error) {
            echo "   - $error\n";
        }
    }
    
    exit(1);
}
