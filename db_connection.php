<?php
/**
 * PERSISTENT DATABASE CONNECTION WRAPPER
 */
require_once __DIR__ . '/app_config.php';

static $globalConnection = null;

if ($globalConnection === null) {
    // Auto-detect environment
    $is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

    if ($is_local) {
        $server = "193.203.168.143";
        $port = 3306;
    } else {
        $server = "localhost";
        $port = 3306;
    }
    
    $user = 'u664421868_blisdatabase';
    $password = 'Blisdata@1234';
    $database = 'u664421868_blisdatabase';
    
    $globalConnection = new mysqli($server, $user, $password, $database, $port);
    
    if ($globalConnection->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $globalConnection->connect_error]));
    }
    
    $globalConnection->set_charset("utf8mb4");
}

$conn = $globalConnection;

register_shutdown_function(function() {
    global $globalConnection;
    if ($globalConnection instanceof mysqli) {
        try {
            @$globalConnection->thread_id;
            $globalConnection->close();
        } catch (Throwable $e) {
            // Already closed
        }
        $globalConnection = null;
    }
});
?>