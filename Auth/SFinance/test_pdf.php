<?php
require '../vendor/autoload.php';

$pdf = new TCPDF\TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Test PDF Works!', 0, 1, 'C');
$pdf->Output('test.pdf', 'D');