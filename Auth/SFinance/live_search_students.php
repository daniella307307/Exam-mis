<?php
include('header.php');

// Set the number of records to display per page
$records_per_page = 100;

if (isset($_GET['STATUS'])) {
    $STATUS = $_GET['STATUS'];
    if ($STATUS == "Active") {
        $status = "Active";
        $action = "student_status ='$status'";
    } else {
        $status = "Inactive";
        $action = "student_status !='Active' OR student_status ='$status'";
    }
} else {
    $STATUS = "Active";
    $status = $STATUS;
    $action = "student_status ='Active'";
}

// Search functionality
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $search_query = " AND (student_regno LIKE '%$search%' OR 
                          student_first_name LIKE '%$search%' OR 
                          student_last_name LIKE '%$search%')";
}

// Fetch records without pagination for live search
$select_user = mysqli_query($conn, "SELECT * FROM student_list
LEFT JOIN countries ON student_list.student_country = countries.id 
WHERE student_school ='$school_ref' AND $action $search_query 
LIMIT $records_per_page");

while($users_details = mysqli_fetch_array($select_user)) {
    $student_id  = $users_details['student_id'];
    $currency = $users_details['Country_currency_code'];
    $find_payment = mysqli_query($conn,"SELECT sum(invc_amount) AS topay FROM school_invoice WHERE invc_student='$student_id' and invc_term ='$setting_term' AND invc_year='$setting_year'");
    
    $inc = mysqli_num_rows($find_payment);
    if($inc > 0) {
        $payments = mysqli_fetch_array($find_payment);
        $topay = (float)$payments['topay'];
        $select_paID = mysqli_query($conn, " SELECT SUM(spay_amount) AS paid  FROM school_payment_details WHERE spay_student ='$student_id' AND spay_year='$setting_year'AND  spay_term='$setting_term'");
        $pay_details = mysqli_fetch_array($select_paID);
        $paid = (float)$pay_details['paid'];
    } else {
        $topay = 0;
        $paid = 0;
    }
    $Balance = $topay - $paid; 
?>
    <tr>
        <td class="border px-4 py-2"><?php echo $users_details['student_regno']; ?></td>
        <td class="border px-4 py-2"><?php echo $users_details['student_first_name']." ".$users_details['student_last_name']; ?></td> 
        <td class="border px-4 py-2 bg-blue-600 text-white"><?php echo number_format($topay,0);?> FRW</td>
        <td class="border px-4 py-2 bg-green-600 text-white"><?php echo number_format($paid,2);?> FRW</td>
        <td class="border px-4 py-2 bg-red-600 text-white"><?php echo number_format($Balance,2);?> FRW</td>
        <td class="border px-4 py-2">
            <a href="Student_invoices?STUDENT=<?php echo $users_details['student_id']; ?>&STATUS=<?php echo $STATUS;?>" class="bg-blue-600 cursor-pointer rounded p-1 mx-1 text-white"> &nbsp;<i class="fas fa-eye"></i></a>
        </td>
    </tr>
<?php
}
?>