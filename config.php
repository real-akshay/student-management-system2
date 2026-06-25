<?php
ini_set('session.cookie_lifetime', 0); // Session cookie, browser close par destroy

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoloader for classes
spl_autoload_register(function ($class) {
    $base_dir = __DIR__ . '/src/';
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }

    // Also check in Models subdirectory
    $model_file = __DIR__ . '/src/Models/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($model_file)) {
        require_once $model_file;
    }
});

// Load environment variables
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[$key] = $value;
        }
    }
}

// MySQL configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'student_management');

// Create MySQL connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Application constants
define('SITE_URL', 'http://localhost/student_management');
define('RECORDS_PER_PAGE', 10);

/**
 * Sanitize user input to prevent XSS attacks
 */
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if admin is logged in
 */
function is_admin_logged_in()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Check if student is logged in
 */
function is_student_logged_in()
{
    return isset($_SESSION['student_id']) && !empty($_SESSION['student_id']);
}

/**
 * Redirect to specified page
 */
function redirect($page)
{
    header("Location: " . $page);
    exit();
}

/**
 * Display alert message using Bootstrap classes
 */
function show_alert($message, $type = 'info')
{
    echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
    echo $message;
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}

/**
 * Format date for display
 */
function format_date($date)
{
    return date('d M Y', strtotime($date));
}

/**
 * Format datetime for display
 */
function format_datetime($datetime)
{
    return date('d M Y h:i A', strtotime($datetime));
}
