<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check for required PHP extensions
if (!function_exists('curl_init')) {
    die("Error: cURL extension is not installed/enabled");
}

// Only process the form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    try {
        // 1. Validate all required fields
        $requiredFields = ['storage_zone', 'access_key'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: " . htmlspecialchars($field));
            }
        }

        // 2. Validate file upload
        if (!isset($_FILES['file_path']) || !is_uploaded_file($_FILES['file_path']['tmp_name'])) {
            throw new Exception("No file was uploaded or upload failed");
        }

        $file = $_FILES['file_path'];

        // 3. Generate safe filename
        $originalName = basename($file['name']);
        $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\-\.]/', '_', $originalName);

        // 4. Configure BunnyCDN connection
        $storageZone = preg_replace('/[^a-zA-Z0-9\-]/', '', $_POST['storage_zone']);
        $accessKey = trim($_POST['access_key']);
        $region = !empty($_POST['region']) ? preg_replace('/[^a-z]/', '', $_POST['region']) . '.' : '';
        
        $hostname = $region . 'storage.bunnycdn.com';
        $url = "https://{$hostname}/{$storageZone}/{$safeName}";

        // 5. Open file handle
        $fileHandle = fopen($file['tmp_name'], 'rb');
        if ($fileHandle === false) {
            throw new Exception("Could not open uploaded file");
        }

        // 6. Initialize and configure cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_PUT => true,
            CURLOPT_INFILE => $fileHandle,
            CURLOPT_INFILESIZE => $file['size'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "AccessKey: {$accessKey}",
                "Content-Type: application/octet-stream"
            ],
        ]);

        // 7. Execute upload
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // 8. Clean up
        fclose($fileHandle);
        curl_close($ch);

        // 9. Handle response
        if ($httpCode === 201) {
            $cdnUrl = "https://{$storageZone}.b-cdn.net/{$safeName}";
            $message = "✅ Upload successful!<br>URL: <a href='{$cdnUrl}' target='_blank'>{$cdnUrl}</a>";
            $messageClass = "success";
        } else {
            throw new Exception("Upload failed with status {$httpCode}. Error: " . htmlspecialchars($error));
        }

    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
        $messageClass = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BunnyCDN File Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .upload-container {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"],
        input[type="file"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #0066cc;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0052a3;
        }
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
        }
        .success {
            background: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<body>
    <h1>Upload File to BunnyCDN</h1>
    
    <div class="upload-container">
        <?php if (isset($message)): ?>
            <div class="message <?= $messageClass ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="region">Region:</label>
                <select id="region" name="region">
                    <option value="">Global (default)</option>
                    <option value="de">Germany</option>
                    <option value="ny">New York</option>
                    <option value="sg">Singapore</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="storage_zone">Storage Zone Name:</label>
                <input type="text" id="storage_zone" name="storage_zone" required 
                       placeholder="bluelakes1988" value="bluelakes1988">
            </div>
            
            <div class="form-group">
                <label for="access_key">API Access Key:</label>
                <input type="password" id="access_key" name="access_key" required 
                       placeholder="From BunnyCDN Dashboard">
            </div>
            
            <div class="form-group">
                <label for="file_path">File to Upload:</label>
                <input type="file" id="file_path" name="file_path" required>
                <small>Max 100MB. Allowed: PDF, DOC, JPG, PNG, MP4</small>
            </div>
            
            <button type="submit" name="upload">Upload to BunnyCDN</button>
        </form>
    </div>
</body>
</html>