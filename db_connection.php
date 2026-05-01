<?php
/**
 * PERSISTENT DATABASE CONNECTION WRAPPER
 * 
 * Use this file instead of including db.php directly to:
 * 1. Reuse connections across multiple includes (reduce from 500+ to ~100/hour)
 * 2. Ensure connections are always closed properly
 * 3. Provide consistent connection handling across all files
 * 
 * Usage:
 *   require_once('db_connection.php');  // Use ONCE, not include
 *   // Now $conn is available
 */

// Use static variable to cache connection across includes
static $globalConnection = null;

if ($globalConnection === null) {
    // Create connection only once per PHP execution
    $server = '193.203.168.143';
    $user = 'u664421868_blisdatabase';
    $password = 'Blisdata@1234';
    $database = 'u664421868_blisdatabase';
    
    $globalConnection = new mysqli($server, $user, $password, $database);
    
    if ($globalConnection->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $globalConnection->connect_error]));
    }
    
    $globalConnection->set_charset("utf8mb4");
}

// Make connection available as $conn throughout this request
$conn = $globalConnection;

// Register shutdown function to close connection at end of script.
// Wrapped in try/catch because pages sometimes also call $conn->close()
// directly, and on PHP 8.1+ closing twice throws mysqli_sql_exception
// during shutdown — which results in a fatal in the apache error log.
register_shutdown_function(function() {
    global $globalConnection;
    if ($globalConnection instanceof mysqli) {
        try {
            // thread_id throws if the connection was already closed; use it
            // as a cheap "is the connection still alive?" probe.
            @$globalConnection->thread_id;
            $globalConnection->close();
        } catch (Throwable $e) {
            // Already closed elsewhere — nothing to do.
        }
        $globalConnection = null;
    }
});

?>
