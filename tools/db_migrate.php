<?php
// Safe DB migration runner for local/staging only
// Usage: php tools/db_migrate.php supabase/migrations/20260614_001_bodhivaas_phase1.sql

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

$cwd = getcwd();
$args = $argv;
array_shift($args);
if (count($args) < 1) {
    echo "Usage: php tools/db_migrate.php <path-to-sql-file>\n";
    exit(1);
}

$sqlFile = $args[0];
if (!file_exists($sqlFile)) {
    // try relative to project root
    $sqlFile = $cwd . DIRECTORY_SEPARATOR . $sqlFile;
}

if (!file_exists($sqlFile)) {
    echo "SQL file not found: " . $argv[1] . "\n";
    exit(1);
}

// Load DB config from project config.php
require_once __DIR__ . '/../config.php';

// Confirm environment (safety)
echo "You are about to run migration: $sqlFile\n";
echo "Database: " . DB_NAME . "@" . DB_HOST . "\n";
echo "Type 'yes' to continue: ";
$handle = fopen('php://stdin', 'r');
$line = trim(fgets($handle));
if ($line !== 'yes') {
    echo "Aborted.\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Failed to read SQL file.\n";
    exit(1);
}

// Use mysqli connection created in config.php ($conn)
if (!isset($conn) || !($conn instanceof mysqli)) {
    // try to create a new connection using constants
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}

if ($conn->connect_error) {
    echo "DB connection failed: " . $conn->connect_error . "\n";
    exit(1);
}

// Split SQL statements safely by delimiter ; but handle DELIMITER not supported
$queries = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));

foreach ($queries as $q) {
    if ($q === '') continue;
    if ($conn->query($q) === false) {
        echo "Query failed: " . $conn->error . "\n";
        echo "Failed SQL snippet:\n" . substr($q, 0, 400) . "\n";
        exit(1);
    }
}

echo "Migration completed successfully.\n";
$conn->close();
