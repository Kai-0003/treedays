<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'gamified_fitness');

try {
    // Create a PDO instance
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Handle connection errors gracefully
    die("<div style='font-family: sans-serif; padding: 2rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin: 2rem auto; max-width: 600px; border: 1px solid #fca5a5;'>
            <h3 style='margin-top:0;'>Database Connection Failed</h3>
            <p>Could not connect to the database <strong>" . DB_NAME . "</strong>.</p>
            <p><em>Error: " . htmlspecialchars($e->getMessage()) . "</em></p>
            <p>Please make sure your MySQL server is running, the database exists, and the credentials in <code>includes/db.php</code> are correct.</p>
         </div>");
}

/**
 * Check if a user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged in user ID
 */
function get_logged_in_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Redirect to login if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit;
    }
}

/**
 * Initialize daily quests for a user if they don't exist for the current date
 */
function initialize_user_quests($db, $user_id) {
    $today = date('Y-m-d');
    
    // Check if user already has quests for today
    $stmt = $db->prepare("SELECT COUNT(*) FROM user_quests WHERE user_id = ? AND quest_date = ?");
    $stmt->execute([$user_id, $today]);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Fetch all available daily quests templates
        $stmt_templates = $db->query("SELECT id FROM daily_quests");
        $quests = $stmt_templates->fetchAll();
        
        if (!empty($quests)) {
            // Insert each daily quest for this user for today
            $stmt_insert = $db->prepare("INSERT INTO user_quests (user_id, quest_id, progress, is_completed, quest_date) VALUES (?, ?, 0, 0, ?)");
            foreach ($quests as $quest) {
                $stmt_insert->execute([$user_id, $quest['id'], $today]);
            }
        }
    }
}
