<?php
// Dynamic PDF proxy - fetches any Bunny PDF and serves it locally
$bunnyAccessKey = '84cc6b36-516d-406e-ac19592187e0-345e-4561';

// Validate URL parameter
$pdfUrl = $_GET['url'] ?? '';
if (empty($pdfUrl)) {
    http_response_code(400);
    die('No URL provided');
}

// Only allow BunnyCDN URLs for security
if (strpos($pdfUrl, 'b-cdn.net') === false && strpos($pdfUrl, 'bunnycdn.com') === false) {
    http_response_code(403);
    die('Only BunnyCDN URLs allowed');
}

// Fetch PDF from Bunny
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "AccessKey: $bunnyAccessKey\r\n"
    ]
];
$context = stream_context_create($options);
$pdfContent = file_get_contents($pdfUrl, false, $context);

if ($pdfContent === false) {
    http_response_code(404);
    die('Failed to fetch PDF');
}

// Serve it as PDF
header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdfContent));
header('Content-Disposition: inline; filename="exam.pdf"');
header('Cache-Control: private, max-age=3600');
echo $pdfContent;
