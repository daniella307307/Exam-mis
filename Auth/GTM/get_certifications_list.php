<?php
header('Content-Type: application/json');
include('../../db.php');

$result = $conn->query("SELECT certification_id, certification_name FROM certifications WHERE certification_status = 'Active' ORDER BY certification_name ASC");
$certifications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $certifications[] = $row;
    }
}
$conn->close();
echo json_encode(['success' => true, 'certifications' => $certifications]);
