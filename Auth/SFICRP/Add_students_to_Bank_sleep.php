<?php
ob_start(); 
include('header.php');

$SLEEP_No = intval($_GET['SLEEP_No'] ?? 0);
$STATUS   = $_GET['STATUS'] ?? "";

// Fetch sleep information securely
$select_user = mysqli_prepare($conn, 
    "SELECT * FROM bank_sleeps
     LEFT JOIN bank_account ON bank_sleeps.sleep_bank_acount = bank_account.acount_id
     LEFT JOIN banks ON bank_sleeps.sleep_bank = banks.bank_id
     WHERE sleep_id = ?"
);
mysqli_stmt_bind_param($select_user, "i", $SLEEP_No);
mysqli_stmt_execute($select_user);
$user_result = mysqli_stmt_get_result($select_user);
$find_sleep = mysqli_fetch_array($user_result);

if(!$find_sleep) {
    die("Sleep record not found");
}
?>

<body class="h-screen font-sans login bg-cover">
<div class="flex flex-1">

    <!--Sidebar-->
    <?php include('dynamic_side_bar.php');?>
    <!--/Sidebar-->

    <!--Main-->
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-4xl">
            <div class="leading-loose">

                <form action="" method="POST" class="max-w-4xl m-4 p-10 bg-white rounded shadow-xl">
                    
<?php
// PROCESS SUBMISSION
if(isset($_POST['Add_Students'])){

    if(empty($_POST['select_student'])){
        echo "<div class='bg-red-200 p-3 rounded text-center mt-3'>No student selected</div>";
    } else {

        foreach($_POST['select_student'] as $sid){

            // Skip if amount is empty
            if(empty($_POST['amount'][$sid])){
                continue;
            }

            // Fetch latest invoice for the student
            $invoice_q = mysqli_query($conn,
                "SELECT * FROM students_invoice 
                 WHERE invoice_student='$sid' 
                 ORDER BY invoice_id DESC LIMIT 1"
            );

            if(mysqli_num_rows($invoice_q) == 0){
                echo "<div class='bg-yellow-200 p-3 rounded mt-2'>
                        No invoice found for student ID: $sid
                      </div>";
                continue;
            }

            $invoice = mysqli_fetch_array($invoice_q);

            $amount = floatval($_POST['amount'][$sid]);
            $invoice_region = $invoice['invoice_region'];
            $usd_amount = floatval($invoice['invoice_usd']); // must be variable
            $pay_invoice = $invoice['invoice_id']; // must be variable

            // Prepare insert statement
            $stmt = mysqli_prepare($conn,
                "INSERT INTO invoice_payments
                (pay_invoice, pay_bank_sleep, payment_student, payment_promotion, 
                 pay_amount_usd, pay_amount_local, payment_country, payment_region, payment_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if(!$stmt){
                echo "<div class='bg-red-300 p-3 mt-2'>Prepare failed: " . mysqli_error($conn) . "</div>";
                continue;
            }

            // Bind parameters using variables only
            mysqli_stmt_bind_param(
                $stmt,
                "siiidddis",
                $pay_invoice,
                $SLEEP_No,
                $invoice['invoice_student'],
                $invoice['invoice_promotion'],
                $usd_amount,
                $amount,
                $invoice['invoice_country'],
                $invoice_region,
                $STATUS
            );

            if(!mysqli_stmt_execute($stmt)){
                echo "<div class='bg-red-300 p-3 mt-2'>Error inserting: " . mysqli_stmt_error($stmt) . "</div>";
            }

            mysqli_stmt_close($stmt);
        }

        echo "<div class='bg-green-200 p-3 rounded text-center mt-3'>
                Selected students added successfully!
              </div>";
    }
}
?>

<p class="text-gray-800 font-medium text-center">
    Add Students to:<br>
    Sleep No: <strong><?php echo htmlspecialchars($find_sleep['sleep_no']); ?></strong><br>
    Account No: <strong><?php echo htmlspecialchars($find_sleep['acount_number']); ?></strong><br>
    Bank: <strong><?php echo htmlspecialchars($find_sleep['bank_name']); ?></strong><br>
</p>

<!-- LIVE SEARCH BAR -->
<div class="mt-4 mb-4 text-center">
    <input id="searchBox" 
           type="text" 
           class="px-3 py-2 border rounded w-1/2" 
           placeholder="Search student name..."
           onkeyup="liveSearch()">
</div>

<table class="table-responsive w-full rounded mt-4" id="studentsTable">
    <thead>
        <tr>
            <th class="border px-4 py-2">Student Name</th>
            <th class="border px-4 py-2">Amount</th>
            <th class="border px-4 py-2">Select</th>
        </tr>
    </thead>

    <tbody>
    <?php
    $select_students = mysqli_query($conn,
        "SELECT * FROM student_list 
         WHERE student_school='$school_ref' AND student_status='Active'"
    );

    while($student = mysqli_fetch_array($select_students)){
        $sid = $student['student_id'];
        $student_name = htmlspecialchars($student['student_first_name'] . " " . $student['student_last_name']);
    ?>
        <tr class="student-row" id="row-<?php echo $sid; ?>" style="display:none;">
            <td class="border px-4 py-1 studentName"><?php echo $student_name; ?></td>

            <td class="border px-4 py-1">
                <input class="w-full px-3 py-1 bg-gray-200 rounded"
                    type="text"
                    name="amount[<?php echo $sid; ?>]"
                    placeholder="Amount">
            </td>

            <td class="border px-4 py-1 text-center">
                <input type="checkbox"
                    onclick="keepVisible(<?php echo $sid; ?>)"
                    name="select_student[]"
                    value="<?php echo $sid; ?>"
                    class="rounded selectBox">
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<div class="mt-6 text-center">
    <button type="submit" name="Add_Students"
        class="px-4 py-2 text-white bg-green-500 rounded hover:bg-green-600 transition">
        Add Selected Students
    </button>
</div>

</form>
</div>
</div>
</div>
</div>

<!-- JAVASCRIPT LIVE SEARCH -->
<script>
function keepVisible(id){
    let row = document.getElementById("row-" + id);
    let checkbox = row.querySelector(".selectBox");

    if(checkbox.checked){
        row.style.display = "";
        row.classList.add("force-visible");
    } else {
        row.classList.remove("force-visible");
        liveSearch();
    }
}

function liveSearch() {
    let input = document.getElementById("searchBox").value.toLowerCase();
    let rows  = document.getElementsByClassName("student-row");

    for(let i = 0; i < rows.length; i++){
        let row = rows[i];
        let name = row.querySelector(".studentName").innerText.toLowerCase();
        let selected = row.classList.contains("force-visible");

        if(selected){
            row.style.display = "";
            continue;
        }

        if(input.length === 0){
            row.style.display = "none";
        } else if(name.includes(input)){
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    }
}
</script>

</body>
</html>
