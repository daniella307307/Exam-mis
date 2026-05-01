<?php
/**
 * Get Schools List - Returns all active schools as JSON
 * Used by exam creator to populate school dropdown
 */

require_once('../db_connection.php');

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT school_id, school_name FROM schools WHERE school_status='Active' ORDER BY school_name ASC");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $schools = [];
    
    while ($row = $result->fetch_assoc()) {
        $schools[] = [
            'school_id' => (int)$row['school_id'],
            'school_name' => $row['school_name']
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'schools' => $schools
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
