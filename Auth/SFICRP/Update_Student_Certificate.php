

<?php
 
include('../../db.php');
include('session.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$STATUS = mysqli_real_escape_string($conn, $_GET['STATUS']);
	$STUDENT  = mysqli_real_escape_string($conn, $_GET['STUDENT']);
    $ID	 =mysqli_real_escape_string($conn, $_GET['ID']);
	$find_details = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM students_promotion
LEFT JOIN  regions_table ON students_promotion.promotion_region=regions_table.region_id
LEFT JOIN  countries ON students_promotion.promotion_country=countries.id
LEFT JOIN  schools ON students_promotion.promotion_school=schools.school_id
LEFT JOIN  certifications ON students_promotion.promotion_certification =certifications.certification_id WHERE promotion_id='$ID'"));
$promotion_certification  = $find_details['promotion_certification'];
$promotion_region = $find_details['promotion_region'];
$promotion_country = $find_details['promotion_country'];
$promotion_school = $find_details['promotion_school'];
$promotion_pay_usd= $find_details['promotion_pay_usd'];
$promotion_pay_local= $find_details['promotion_pay_local'];
$insert =mysqli_query($conn,"INSERT INTO students_invoice (invoice_id,invoice_student,invoice_certificate,invoice_promotion,invoice_region,invoice_country,invoice_school,invoice_usd,invoice_local,invoice_status) VALUES 
                                                                (NULL,'$STUDENT','$promotion_certification','$ID','$promotion_region','$promotion_country','$promotion_school','$promotion_pay_usd','$promotion_pay_local','Active')");
if($insert){
	 
$Update =mysqli_query($conn,"UPDATE  student_list SET  student_promotion = '$ID' WHERE  student_id =$STUDENT");	
if($Update){
	 
	?> 
	 <script>window.setTimeout(function(){

        // Move to a new location or you can do something else
        window.location.href = "Assign_Students_in_promotion?ID=<?php echo $ID;?>&STATUS=<?php echo $STATUS;?>";

    }, 10);</script> 
	<?php 
}

}
}
 
 
		
		
	 

?>