<?php
// session.php should handle session_start properly
include('session.php');

require '../vendor/autoload.php'; // Path to Composer autoload
require_once('tcpdf/tcpdf.php'); // Adjust the path based on where you uploaded it


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Tcpdf;

// Validate & sanitize format input
$format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : '';
if (!in_array($format, ['excel', 'pdf'])) {
    die('Invalid format specified');
}

// Make sure $conn and $school_ref are set in session.php or earlier
if (!isset($conn, $school_ref)) {
    die('Database connection or school reference is missing.');
}

$school_ref_safe = mysqli_real_escape_string($conn, $school_ref);
$query = "SELECT * FROM student_list WHERE student_school = '$school_ref_safe'";
$select_user = mysqli_query($conn, $query);

if (!$select_user) {
    die('Query failed: ' . mysqli_error($conn));
}

if ($format === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'REG No');
    $sheet->setCellValue('B1', 'Names');
    $sheet->setCellValue('C1', 'Gender');
    $sheet->setCellValue('D1', 'Dob');
    $sheet->setCellValue('F1', 'Status');

    $row = 2;
    while ($user = mysqli_fetch_assoc($select_user)) {
        $sheet->setCellValue('A' . $row, $user['student_regno']);
        $sheet->setCellValue('B' . $row, $user['student_first_name'] . " " . $user['student_last_name']);
        $sheet->setCellValue('C' . $row, $user['student_gender']);
        $sheet->setCellValue('D' . $row, $user['student_dob']);
        $sheet->setCellValue('F' . $row, $user['student_status']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'Students_List.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;

} elseif ($format === 'pdf') {
    // TCPDF should be available via autoload; no need to include manually if configured
    $pdf = new \TCPDF();
    $pdf->AddPage();

    $html = '<h2>ICRPlus Students List</h2>
    <h2>School Name :<big><strong> '.$school_name.'</strong></big></h2>
    <h2>Year :'.DATE("Y").'</h2>
             <table border="1" cellpadding="5">
             <thead>
             <tr>
             <th>REG No</th>
             <th>Names</th>
             <th>Gender</th>
             <th>DOB</th> 
             <th>Status</th>
             </tr>
             </thead>
             <tbody>';

    mysqli_data_seek($select_user, 0); // Rewind result set
    while ($user = mysqli_fetch_assoc($select_user)) {
        $html .= '<tr>
                  <td>' . htmlspecialchars($user['student_regno']) . '</td>
                  <td>' . htmlspecialchars($user['student_first_name'] . ' ' . $user['student_last_name']) . '</td>
                  <td>' . htmlspecialchars($user['student_gender']) . '</td>
                  <td>' . htmlspecialchars($user['student_dob']) . '</td>
                  <td>' . htmlspecialchars($user['student_status']) . '</td>
                  </tr>';
    }

    $html .= '</tbody></table>';

    $pdf->writeHTML($html);
    $pdf->Output('Students_List.pdf', 'D');
    exit;
}

mysqli_close($conn);
?>
