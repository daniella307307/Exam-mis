<?php
ob_start();
include('header.php');

$setting_min_year = 6; // uncommented and defined

$url = $_GET['CALL_BACK'] ?? "Deposite_Amount";

// Calculate the date $setting_min_year years ago
$minDate = date('Y-m-d', strtotime("-$setting_min_year years"));

?>

<!DOCTYPE html>
<html>
<head>
    <title>Update User Details</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
</head>
<body class="h-screen font-sans login bg-cover">
<script>
function getstate(val) {
    $.ajax({
        type: "POST",
        url: "get_acount.php",
        data: { coutrycode: val },
        success: function(data){
            $("#statelist").html(data);
        }
    });
}

function getcity(val) {
    $.ajax({
        type: "POST",
        url: "get_city.php",
        data: { statecode: val },
        success: function(data){
            $("#city").html(data);
        }
    });
}
</script>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>
    
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">

                    <p class="text-gray-800 font-medium">Add New Bank Sleep</p>

                    <?php
                    if(isset($_POST['Update'])){
                        $bank_id = mysqli_real_escape_string($conn, $_POST['bank_id']);
                        $Account = mysqli_real_escape_string($conn, $_POST['Account']);
                        $sleep_no = mysqli_real_escape_string($conn, $_POST['sleep_no']);
                        $sleep_date = mysqli_real_escape_string($conn, $_POST['sleep_date']);
                        $sleep_amount_local = mysqli_real_escape_string($conn, $_POST['sleep_amount_local']);

                        $select_bank = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM banks  
                            LEFT JOIN countries ON banks.bank_country = countries.id WHERE bank_id='$bank_id'"));
                        
                        $bank_country = $select_bank['bank_country'];
                        $bank_region = $select_bank['bank_region'];
                        $unity_usd = $select_bank['currency_usd'];
                        $sleep_amount_usd = $sleep_amount_local / $unity_usd;

                        $select_sleep = mysqli_query($conn,"SELECT * FROM bank_sleeps 
                            WHERE sleep_no ='$sleep_no' AND sleep_country ='$bank_country'");
                        $sleep_count = mysqli_num_rows($select_sleep); 

                        if($sleep_count > 0){
                            echo '<div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative">
                                <strong class="font-bold">Duplicate Error</strong>
                                <span class="block sm:inline">This Bank sleep ('.$sleep_no.') already exists! Try a different number.</span>
                            </div>';
                        } else {
                            $school_ref = $_SESSION['school_ref'] ?? '1'; // define school_ref
                            $insert = mysqli_query($conn, "INSERT INTO bank_sleeps 
                                (sleep_id, sleep_bank, sleep_bank_acount, sleep_school, sleep_country, sleep_region, sleep_no, sleep_amount_usd, sleep_amount_local, sleep_date, sleep_document, sleep_status)
                                VALUES (NULL, '$bank_id', '$Account', '$school_ref', '$bank_country', '$bank_region', '$sleep_no', '$sleep_amount_usd', '$sleep_amount_local', '$sleep_date', 'document', 'Waiting')");

                            if($insert){
                                echo '<div class="bg-green-500 mb-2 border border-green-300 text-white px-4 py-3 rounded relative">
                                    <strong class="font-bold">Success!</strong>
                                    <span class="block sm:inline">This Bank Sleep has been added.</span>
                                </div>
                                <script>
                                    setTimeout(function(){
                                        window.location.href = "'.$url.'";
                                    }, 500);
                                </script>';
                            } else {
                                echo '<div class="bg-red-500 mb-2 border border-red-300 text-white px-4 py-3 rounded relative">
                                    <strong class="font-bold">Oops!</strong>
                                    <span class="block sm:inline">Internal Server Error.</span>
                                </div>';
                            }
                        }
                    }
                    ?>

                    <!-- Bank Selection -->
                    <div class="w-full mb-6">
                        <label class="block text-gray-700 text-sm font-light mb-1">Bank</label>
                        <select name="bank_id" onChange="getstate(this.value);" id="country" class="block w-full bg-gray-200 border rounded px-4 py-2" required>
                            <option value="">=== SELECT BANK ===</option>
                            <?php
                            $select_school = mysqli_query($conn, "SELECT * FROM banks WHERE bank_country='$user_country' AND bank_status='Active'");
                            while ($find_school = mysqli_fetch_array($select_school)) {
                                echo '<option value="'.$find_school['bank_id'].'">'.$find_school['bank_name'].'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Bank Account -->
                    <div class="w-full mb-6">
                        <label class="block text-gray-700 text-sm font-light mb-1">Bank Account</label>
                        <select name="Account" id="statelist" onChange="getcity(this.value);" class="block w-full bg-gray-200 border rounded px-4 py-2" required>
                            <option value="">=== SELECT ACCOUNT ===</option>
                        </select>
                    </div>

                    <!-- Deposit Date -->
                    <div class="w-full mb-6">
                        <label class="block text-gray-700 text-sm mb-1">Deposit Date</label>
                        <input type="date" name="sleep_date" max="<?php echo date("Y-m-d"); ?>" class="w-full px-4 py-2 rounded bg-gray-200" required>
                    </div>

                    <!-- Bank Sleep No -->
                    <div class="w-full mb-6">
                        <label class="block text-gray-700 text-sm mb-1">Bank Sleep No</label>
                        <input type="text" name="sleep_no" class="w-full px-4 py-2 rounded bg-gray-200" required>
                    </div>

                    <!-- Amount -->
                    <div class="w-full mb-6">
                        <label class="block text-gray-700 text-sm mb-1">Amount</label>
                        <input type="number" name="sleep_amount_local" class="w-full px-4 py-2 rounded bg-gray-200" required>
                    </div>

                    <center>
                        <div class="mt-4">
                            <button type="submit" name="Update" class="px-4 py-2 bg-green-500 text-white rounded">Add Deposit Amount</button>
                        </div>
                    </center>

                </form>
            </div>
        </div>
    </div>
</div>

<script src="js/bootstrap.min.js"></script>
</body>
</html>

<?php
ob_end_flush();
?>
