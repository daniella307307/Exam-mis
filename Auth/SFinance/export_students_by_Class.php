<?php
// Start output buffering
ob_start();
include('session.php');

// Manual TCPDF inclusion
require_once '../TCPDF/tcpdf.php'; // Path to TCPDF

// For Excel (keep existing)
 
  $CLASS = $_GET['CLASS'];
 $format = $_GET['format'];
 
 
 if (isset($_GET['format']) && $_GET['format'] === 'excel') {
    ob_end_clean();

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=students_list.xls");
    
    header("Pragma: no-cache");
    header("Expires: 0");

    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<tr>
            <th>Registration Number</th>
            <th>Full Name</th>
            <th>Gender</th>
            <th>Date of Birth</th>
            <th>Contact</th>
            <th>Status</th>
          </tr>';

    $query = "SELECT * FROM student_list WHERE student_school='$school_ref' AND student_class=' $CLASS'";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>
                <td>' . htmlspecialchars($row['student_regno']) . '</td>
                <td>' . htmlspecialchars($row['student_first_name'] . ' ' . $row['student_last_name']) . '</td>
                <td>' . htmlspecialchars($row['student_gender']) . '</td>
                <td>' . htmlspecialchars($row['student_dob']) . '</td>
                <td>' . htmlspecialchars($row['student_contact']) . '</td>
                <td>' . htmlspecialchars($row['student_status']) . '</td>
              </tr>';
    }

    echo '</table>';
    echo '</body></html>';
    exit;
}



// For PDF
elseif (isset($_GET['format']) && $_GET['format'] === 'pdf') {
    // Clear any previous output
    ob_end_clean();

    // Create PDF instance
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Basic PDF setup
    $pdf->SetCreator('Your School');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('Students List');
    $pdf->AddPage();

    // Simple table header
    $html = '<h2>Students List</h2>
            <table border="1" cellpadding="5">
                <tr>
                    <th>Reg No</th>
                    <th>Name</th>
                    <th>Gender</th>
                </tr>';

    // Add data
    $query = "SELECT * FROM student_list WHERE student_school = '".mysqli_real_escape_string($conn, $school_ref)."'";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $html .= '<tr>
                    <td>'.htmlspecialchars($row['student_regno']).'</td>
                    <td>'.htmlspecialchars($row['student_first_name'].' '.$row['student_last_name']).'</td>
                    <td>'.htmlspecialchars($row['student_gender']).'</td>
                  </tr>';
    }

    $html .= '</table>';

    // Generate PDF
    $pdf->writeHTML($html);
    $pdf->Output('students.pdf', 'D');
    exit;
}

// Cleanup
mysqli_close($conn);