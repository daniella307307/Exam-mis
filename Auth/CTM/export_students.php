<?php
include('session.php');
require '../vendor/autoload.php'; // Autoload PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

 

$format = $_GET['format'];

$select_user = mysqli_query($conn, "SELECT * FROM student_list WHERE student_school ='$school_ref'");

if ($format == 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'REG No');
    $sheet->setCellValue('B1', 'Names');
    $sheet->setCellValue('C1', 'Gender');
    $sheet->setCellValue('D1', 'Dob');
    $sheet->setCellValue('E1', 'Contact');
    $sheet->setCellValue('F1', 'Status');

    $row = 2;
    while ($users_details = mysqli_fetch_array($select_user)) {
        $sheet->setCellValue('A' . $row, $users_details['student_regno']);
        $sheet->setCellValue('B' . $row, $users_details['student_first_name'] . " " . $users_details['student_last_name']);
        $sheet->setCellValue('C' . $row, $users_details['student_gender']);
        $sheet->setCellValue('D' . $row, $users_details['student_dob']);
        $sheet->setCellValue('E' . $row, $users_details['student_contact']);
        $sheet->setCellValue('F' . $row, $users_details['student_status']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'Students_List.xlsx';

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
} elseif ($format == 'pdf') {
    require_once('../vendor/tecnickcom/tcpdf/tcpdf.php'); // Ensure the correct path to TCPDF

    $pdf = new TCPDF();
    $pdf->AddPage();
    $html = '<h1>Students List</h1>
             <table border="1" cellpadding="5">
             <thead>
             <tr>
             <th>REG No</th>
             <th>Names</th>
             <th>Gender</th>
             <th>Dob</th>
             <th>Contact</th>
             <th>Status</th>
             </tr>
             </thead>
             <tbody>';

    while ($users_details = mysqli_fetch_array($select_user)) {
        $html .= '<tr>
                  <td>' . $users_details['student_regno'] . '</td>
                  <td>' . $users_details['student_first_name'] . ' ' . $users_details['student_last_name'] . '</td>
                  <td>' . $users_details['student_gender'] . '</td>
                  <td>' . $users_details['student_dob'] . '</td>
                  <td>' . $users_details['student_contact'] . '</td>
                  <td>' . $users_details['student_status'] . '</td>
                  </tr>';
    }

    $html .= '</tbody></table>';
    $pdf->writeHTML($html);
    $pdf->Output('Students_List.pdf', 'D');
    exit;
}

$conn->close();
?>
