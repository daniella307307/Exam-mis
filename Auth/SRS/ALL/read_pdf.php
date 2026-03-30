<?php
// Example: read_pdf.php?file=https://example.com/files/mydocument.pdf

// 1. Get the file URL from query string
if (!isset($_GET['url'])) {
    die("No file specified.");
}

$file_url = $_GET['url'];

// 2. Validate that it is a PDF link
if (pathinfo($file_url, PATHINFO_EXTENSION) !== 'pdf') {
    die("Invalid file type.");
}

// 3. Set a safe custom filename (instead of original)
$custom_filename = "Protected_Document_" . time() . ".pdf";

// 4. Send headers to force download
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"$custom_filename\"");
header("Content-Transfer-Encoding: binary");
header("Accept-Ranges: bytes");

// 5. Read file from external URL and output it
readfile($file_url);
exit;
?>
