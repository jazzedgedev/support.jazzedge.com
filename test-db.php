<?php
// Test database connection and queries
require_once 'wp-config.php';

// Test the database class
require_once 'plugins/academy-lesson-manager/includes/class-database.php';

echo "Testing database queries...\n\n";

// Test courses
echo "=== COURSES ===\n";
$courses = ALM_Database::get_courses();
echo "Found " . count($courses) . " courses\n";
if (!empty($courses)) {
    echo "First course: " . print_r($courses[0], true) . "\n";
}

// Test lessons  
echo "\n=== LESSONS ===\n";
$lessons = ALM_Database::get_lessons();
echo "Found " . count($lessons) . " lessons\n";
if (!empty($lessons)) {
    echo "First lesson: " . print_r($lessons[0], true) . "\n";
}

// Test chapters
echo "\n=== CHAPTERS ===\n";
$chapters = ALM_Database::get_chapters();
echo "Found " . count($chapters) . " chapters\n";
if (!empty($chapters)) {
    echo "First chapter: " . print_r($chapters[0], true) . "\n";
}
