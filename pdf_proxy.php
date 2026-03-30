<?php
require_once('Auth/SF/TCPDF/tcpdf.php');

// 1. Verify the request is coming from your server
$allowed_referer = 'https://bluelackesadigital.com';
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $allowed_referer) === false) {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access not allowed');
}

// 2. Bunny.net CDN URL with Access Key
$bunnyPdfUrl = 'https://bluelakes1988.b-cdn.net/courses/nursary_primary/NURSARY_PROGRAM/Nursary2/Exploring%20Robots%20and%20Sensors/WEEK3.pdf';
$bunnyAccessKey = '84cc6b36-516d-406e-ac19592187e0-345e-4561'; // Get this from Bunny.net dashboard

// 3. Initialize TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetProtection([], null, null, 0); // Disable all permissions

// 4. Download from Bunny.net with proper headers
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "AccessKey: $bunnyAccessKey\r\n"
    ]
];
$context = stream_context_create($options);

try {
    // 5. Get PDF content
    $pdfContent = file_get_contents($bunnyPdfUrl, false, $context);
    if ($pdfContent === false) {
        throw new Exception("Failed to download PDF from Bunny.net");
    }

    // 6. Create temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'bunnypdf');
    file_put_contents($tempFile, $pdfContent);

    // 7. Process with TCPDF
    $pageCount = $pdf->setSourceFile($tempFile);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);
        $orientation = ($size['w'] > $size['h']) ? 'L' : 'P';
        $pdf->AddPage($orientation);
        $pdf->useTemplate($templateId);
        
        // Add watermark
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(200, 200, 200);
        $pdf->SetAlpha(0.2);
        $pdf->Rotate(45, 85, 100);
        $pdf->Text(30, 30, "CONFIDENTIAL - " . date('Y-m-d'));
        $pdf->Rotate(0);
        $pdf->SetAlpha(1);
    }

    // 8. Output PDF
    $pdf->Output('document.pdf', 'I');

} catch (Exception $e) {
    // Error handling
    header('Content-Type: text/html');
    echo "<h2>Error Loading Document</h2>";
    echo "<p>".htmlspecialchars($e->getMessage())."</p>";
    echo "<p>Please try again or contact support.</p>";
} finally {
    // Clean up
    if (isset($tempFile) && file_exists($tempFile)) {
        unlink($tempFile);
    }
}