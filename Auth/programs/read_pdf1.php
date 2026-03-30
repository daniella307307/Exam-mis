 




<?php
$file = '../Courses/ElectricityWEEK1.pdf';

header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($file));

// Bypass all PHP/Apache restrictions
readfile($file);
exit;