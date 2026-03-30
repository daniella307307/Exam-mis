<?php
ob_start();
include('header.php');

$COUNTRY = mysqli_real_escape_string($conn, $_GET['COUNTRY']);
$Bank    = mysqli_real_escape_string($conn, $_GET['Bank']);

// Fetch bank
$select_bank = mysqli_query($conn, "SELECT * FROM banks WHERE bank_id='$Bank'");
$find_bank   = mysqli_fetch_array($select_bank);

// Status logic
if (isset($_GET['STATUS'])) {
    $STATUS = $_GET['STATUS'];

    if ($STATUS == "Inactive") {
        $action = "(acount_status='$STATUS' OR acount_status!='Active')";
    } else {
        $action = "acount_status='$STATUS'";
    }
} else {
    $STATUS = "Active";
    $action = "acount_status='Active'";
}

// Country details
$country_details = mysqli_fetch_array(mysqli_query(
    $conn,
    "SELECT * FROM countries 
     LEFT JOIN regions_table ON countries.Country_region = regions_table.region_id 
     WHERE id='$COUNTRY'"
));

// Select accounts
$select_bank_account = mysqli_query(
    $conn,
    "SELECT * FROM bank_account 
     LEFT JOIN banks ON bank_account.acount_bank = banks.bank_id  
     WHERE acount_bank='$Bank' AND $action"
);

$inc = mysqli_num_rows($select_bank_account);
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->

    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">

            <!-- Buttons -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2"></div>
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">

                        <a href="Accounts_in_Banks?STATUS=Active&Bank=<?php echo $Bank; ?>&COUNTRY=<?php echo $COUNTRY; ?>">
                            <button class="bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">Active</button>
                        </a>

                        <a href="Accounts_in_Banks?STATUS=Inactive&Bank=<?php echo $Bank; ?>&COUNTRY=<?php echo $COUNTRY; ?>">
                            <button class="bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded">Inactive</button>
                        </a>

                        <a href="Add_New_Bank.php?COUNTRY=<?php echo $COUNTRY; ?>">
                            <button class="bg-yellow-400 hover:bg-blue-400 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-plus"></i> &nbsp;Add
                            </button>
                        </a>

                        <button onclick="window.print();" class="bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-print"></i> &nbsp;Print
                        </button>

                    </div>

                    <!-- TABLE -->
                    <div class="p-3">
                        <center>
                            <big>
                                <strong><?php echo $STATUS; ?></strong>
                                Bank Accounts in <strong><?php echo $find_bank['bank_name']; ?></strong>
                            </big>
                        </center>
                        <br>

                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">#</th>
                                    <th class="border w-1/8 px-4 py-2">Account Number</th>
                                    <th class="border w-1/8 px-4 py-2">Status</th>
                                    <th class="border w-1/9 px-4 py-2">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($bank_Account_details = mysqli_fetch_array($select_bank_account)) {

                                    $acount_id     = $bank_Account_details['acount_id'];
                                    $acount_status = $bank_Account_details['acount_status'];

                                    if ($acount_status == "Active") {
                                        $link = "Update_bank_Account_status?COUNTRY=$COUNTRY&ACOUNT=$acount_id&Bank=$Bank&CURRENT=$acount_status&STATUS=Inactive";
                                        $bG   = "bg-red-500";
                                        $icon = "fa-lock";
                                    } else {
                                        $link = "Update_bank_Account_status?COUNTRY=$COUNTRY&ACOUNT=$acount_id&Bank=$Bank&CURRENT=$acount_status&STATUS=Active";
                                        $bG   = "bg-green-500";
                                        $icon = "fa-unlock";
                                    }
                                ?>
                                    <tr>
                                        <td class="border px-4 py-2"><?php echo $acount_id; ?></td>
                                        <td class="border px-4 py-2"><?php echo $bank_Account_details['acount_number']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $bank_Account_details['acount_status']; ?></td>

                                        <td class="border px-4 py-2">
                                            <a href="<?php echo $link; ?>" class="<?php echo $bG; ?> cursor-pointer rounded p-1 mx-1 text-white">
                                                &nbsp;<i class="fas <?php echo $icon; ?>"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!--Footer-->
<?php include('footer.php'); ?>
<!--/footer-->

<script src="../../main.js"></script>

</body>
</html>
