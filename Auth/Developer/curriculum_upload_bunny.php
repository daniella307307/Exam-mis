<?php
/**
 * Developer-only direct Bunny CDN upload for curriculum content.
 *
 * Mirrors the working pattern from exams/upload_to_bunny.php (PUT the file
 * binary to https://storage.bunnycdn.com/<zone>/<path> with AccessKey
 * header). Differences:
 *
 *   - role-gated to Developer (session.php enforces $permissio_location)
 *   - accepts BOTH PDF (kind=pdf) and video (kind=video, mp4/webm)
 *   - path is scoped by (certification, term, month, week) so files land
 *     in a predictable Bunny folder tree the Developer can reorganise:
 *         curriculum/cert{ID}/term{T}/m{MM}/w{W}/{kind}-{ts}.{ext}
 *
 * POST (multipart/form-data):
 *   kind   : 'pdf' | 'video'
 *   cert   : (int) certification_id
 *   term   : (int) 1..3
 *   month  : (int) 1..12
 *   week   : (int) 1..6
 *   file   : the actual file (form field name)
 *
 * Response: { success: bool, url?: string, filename?: string, error?: string }
 */
require_once(__DIR__ . '/session.php');
require_once(__DIR__ . '/../curriculum_helpers.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST only']);
    exit;
}

if (strcasecmp((string)($permissio_location ?? ''), 'Developer') !== 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Developer access only']);
    exit;
}

try {
    // --- Bunny config (same zone the exams uploader uses; production credentials)
    $bunnyApiKey      = '84cc6b36-516d-406e-ac19592187e0-345e-4561';
    $bunnyStorageZone = 'bluelakes';
    $bunnyHostname    = 'bluelakes1988.b-cdn.net';
    $bunnyStorageUrl  = 'storage.bunnycdn.com';

    $kind  = strtolower(trim((string)($_POST['kind'] ?? '')));
    $cert  = (int)($_POST['cert']  ?? 0);
    $term  = (int)($_POST['term']  ?? 0);
    $month = (int)($_POST['month'] ?? 0);
    $week  = (int)($_POST['week']  ?? 0);

    if (!in_array($kind, ['pdf', 'video'], true)) {
        throw new Exception('kind must be "pdf" or "video"');
    }
    if ($cert <= 0 || !curriculum_valid_slot($term, $month, $week)) {
        throw new Exception('Bad slot (cert/term/month/week)');
    }
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $code = $_FILES['file']['error'] ?? 'no file';
        throw new Exception('Upload failed (code ' . $code . '). Check upload_max_filesize / post_max_size in php.ini.');
    }

    $file     = $_FILES['file'];
    $tmp      = $file['tmp_name'];
    $orig     = (string)$file['name'];
    $size     = (int)$file['size'];
    $mime     = function_exists('mime_content_type') ? @mime_content_type($tmp) : '';

    // Per-kind MIME + size enforcement.
    if ($kind === 'pdf') {
        if ($mime !== 'application/pdf' && pathinfo($orig, PATHINFO_EXTENSION) !== 'pdf') {
            throw new Exception('Only PDF files accepted for kind=pdf (got ' . htmlspecialchars($mime ?: 'unknown') . ')');
        }
        $max = 150 * 1024 * 1024; // 150MB ceiling for lesson PDFs
        $ext = 'pdf';
        $content_type = 'application/pdf';
    } else {
        // video — accept common browser-playable containers.
        $allowed_video = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-m4v'];
        $allowed_ext   = ['mp4', 'webm', 'mov', 'm4v'];
        $ext_in        = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($mime, $allowed_video, true) && !in_array($ext_in, $allowed_ext, true)) {
            throw new Exception('Video must be mp4 / webm / mov (got ' . htmlspecialchars($mime ?: 'unknown') . ')');
        }
        $max = 800 * 1024 * 1024; // 800MB ceiling per lesson video
        $ext = in_array($ext_in, $allowed_ext, true) ? $ext_in : 'mp4';
        $content_type = $mime ?: 'video/mp4';
    }

    if ($size <= 0)        { throw new Exception('Empty file'); }
    if ($size > $max)      { throw new Exception('File too large: ' . number_format($size / 1024 / 1024, 1) . 'MB > limit ' . ($max / 1024 / 1024) . 'MB'); }

    // Human-readable Bunny path so the Developer can navigate the storage
    // bucket without decoding cert IDs / month numbers:
    //     curriculum/Nursery_I/Term_1/January/Week_1/pdf-2026...-counting.pdf
    //
    // Cert NAME comes from the certifications row; sanitized to A-Z/0-9/_-.
    // If the row is missing (shouldn't happen — earlier slot check passed),
    // fall back to cert{ID} so the upload still goes through.
    $cstmt = $conn->prepare("SELECT certification_name FROM certifications WHERE certification_id = ? LIMIT 1");
    $cstmt->bind_param('i', $cert);
    $cstmt->execute();
    $cert_row = $cstmt->get_result()->fetch_assoc();
    $cstmt->close();
    $cert_label  = $cert_row && !empty($cert_row['certification_name'])
                     ? curriculum_safe_path_segment((string)$cert_row['certification_name'])
                     : 'cert' . $cert;

    $month_names = curriculum_month_names();
    $month_label = curriculum_safe_path_segment($month_names[$month] ?? ('Month_' . $month));

    $timestamp = date('Ymd_His');
    $safe_orig = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
    $safe_orig = substr((string)$safe_orig, 0, 60);
    $filename  = $kind . '-' . $timestamp . '-' . $safe_orig . '.' . $ext;
    $bunny_path = sprintf(
        'curriculum/%s/Term_%d/%s/Week_%d/%s',
        $cert_label, $term, $month_label, $week, $filename
    );

    // Stream the file binary up. file_get_contents into memory is fine for
    // <=800MB on a server with matching memory_limit; if you ever push that
    // limit, switch to chunked read + CURLOPT_READFUNCTION.
    $body = file_get_contents($tmp);
    if ($body === false) { throw new Exception('Failed to read uploaded file'); }

    $put_url = "https://{$bunnyStorageUrl}/{$bunnyStorageZone}/{$bunny_path}";
    $ch = curl_init($put_url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 600,    // big videos take time
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_HTTPHEADER     => [
            "AccessKey: {$bunnyApiKey}",
            "Content-Type: {$content_type}",
            'Content-Length: ' . strlen($body),
        ],
    ]);
    $response = curl_exec($ch);
    $http     = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    $errno    = curl_errno($ch);
    curl_close($ch);

    if ($errno !== 0 || ($http !== 200 && $http !== 201)) {
        error_log("[CURRICULUM_BUNNY] upload failed http=$http errno=$errno err=$err resp=$response");
        throw new Exception("Bunny upload failed (HTTP $http) — $err");
    }

    $public_url = "https://{$bunnyHostname}/{$bunny_path}";
    echo json_encode([
        'success'  => true,
        'url'      => $public_url,
        'filename' => $filename,
        'path'     => $bunny_path,
        'size'     => $size,
        'kind'     => $kind,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
