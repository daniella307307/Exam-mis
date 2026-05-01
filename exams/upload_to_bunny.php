<?php
/**
 * BUNNY CDN PDF UPLOAD HANDLER
 * Uploads practical exam PDF files directly to Bunny CDN
 * PRODUCTION MODE: No fallback - Bunny upload required
 * Returns: {success: true, url: "https://...", filename: "...", storage: "bunny"}
 */

require_once('../db_connection.php');

header('Content-Type: application/json');

try {
    // Bunny API credentials
    $bunnyApiKey = '84cc6b36-516d-406e-ac19592187e0-345e-4561';
    $bunnyStorageZone = 'bluelakes';
    $bunnyHostname = 'bluelakes1988.b-cdn.net';
    $bunnyStorageUrl = 'storage.bunnycdn.com';

    error_log("[BUNNY_UPLOAD] Using storage endpoint: $bunnyStorageUrl");

    // Validate school_id
    $school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;
    if (!$school_id) {
        throw new Exception('School ID is required');
    }

    $schoolStmt = $conn->prepare("SELECT school_name FROM schools WHERE school_id = ? LIMIT 1");
    $schoolStmt->bind_param("i", $school_id);
    $schoolStmt->execute();
    $schoolResult = $schoolStmt->get_result()->fetch_assoc();
    $schoolStmt->close();

    if (!$schoolResult) {
        throw new Exception('School not found');
    }

    $school_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $schoolResult['school_name']);

    // Validate file upload
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('PDF file upload failed: ' . ($_FILES['pdf_file']['error'] ?? 'Unknown error'));
    }

    $file = $_FILES['pdf_file'];
    $filename = basename($file['name']);
    $filesize = filesize($file['tmp_name']);
    $mime_type = mime_content_type($file['tmp_name']);

    // Validate file type
    if ($mime_type !== 'application/pdf') {
        throw new Exception('Only PDF files are allowed. Detected type: ' . $mime_type);
    }

    // Validate file size (max 100MB)
    if ($filesize > 100 * 1024 * 1024) {
        throw new Exception('File size exceeds 100MB limit');
    }

    // Generate unique filename with timestamp
    $timestamp = date('Y-m-d_His');
    $unique_filename = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

    $bunny_path = "exams/{$school_name}/{$unique_filename}";
    $file_content = file_get_contents($file['tmp_name']);
    if ($file_content === false) {
        throw new Exception('Failed to read file content');
    }

    // Try Bunny CDN upload
    $url = "https://{$bunnyStorageUrl}/{$bunnyStorageZone}/{$bunny_path}";
    error_log("[BUNNY_UPLOAD] Attempting Bunny upload: $url");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $file_content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $headers = [
        'Content-Type: application/pdf',
        "AccessKey: {$bunnyApiKey}",
        'Content-Length: ' . strlen($file_content)
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    error_log("[BUNNY_UPLOAD] HTTP Code: $http_code, Error: $curl_error, Errno: $curl_errno");

    if ($curl_errno === 0 && ($http_code === 201 || $http_code === 200)) {
        // Bunny upload successful
        $bunny_url = "https://{$bunnyHostname}/{$bunny_path}";
        error_log("[BUNNY_UPLOAD] Success: {$bunny_url}");

        echo json_encode([
            'success' => true,
            'url' => $bunny_url,
            'filename' => $unique_filename,
            'path' => $bunny_path,
            'filesize' => $filesize,
            'storage' => 'bunny',
            'message' => 'PDF uploaded successfully to Bunny CDN'
        ]);
        exit;
    } else {
        // Bunny upload FAILED - no fallback in production!
        throw new Exception("Failed to upload to Bunny CDN. HTTP Status: {$http_code}, Error: {$curl_error} (Errno: {$curl_errno}). Check network connectivity and Bunny credentials.");
    }

} catch (Exception $e) {
    error_log("[BUNNY_UPLOAD] Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
