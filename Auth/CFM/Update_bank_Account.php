<?php
ob_start(); 
include('header.php');

$BANK = $_GET['BANK'];
$ACCOUNT_ID = $_GET['ACCOUNT_ID']; // <-- added account ID to update

$select_bank = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM banks WHERE bank_id='$BANK'"));

// Get account details to edit
$account_info = mysqli_fetch_array(mysqli_query($conn,"
    SELECT * FROM bank_account WHERE acount_id='$ACCOUNT_ID'
"));
?>
<!--/Header-->

<div class="flex flex-1">
<?php include('dynamic_side_bar.php');?>

<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
    <div class="w-full max-w-lg">
        <div class="leading-loose">

<form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
    <p class="text-gray-800 font-medium">
       <strong>Update Account Details in <br> <?php echo $select_bank['bank_name'];?> </strong>
    </p>

    <div class="">
        <label class="block text-sm text-gray-600" for="acount_number">Account Number</label>
        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
               id="acount_number"
               name="acount_number"
               type="text"
               value="<?php echo $account_info['acount_number'];?>"
               required>
    </div>
 
    
    	<div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           Status 
                        </label>  
                        <div class="relative">
                            <select name="acount_status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
							 <option value="<?php echo $account_info['acount_status'];?>"><?php echo $account_info['acount_status'];?></option> 
                             <option value="Active">Active</option> 
                             <option value="Inactive">Inactive</option>
                             <option value="Deleted">Delete</option>
                             <option value="Blocked">Block</option> 
                            </select>
                             
                        </div>
                    </div>

<?php
// HANDLE UPDATE FORM
if(isset($_POST['Update_account'])){

    $acount_number  = mysqli_real_escape_string($conn,$_POST['acount_number']);
    $acount_status  = mysqli_real_escape_string($conn,$_POST['acount_status']);

    $count = mysqli_num_rows(mysqli_query($conn,"
        SELECT * FROM bank_account 
        WHERE acount_bank='$BANK'
        AND acount_number='$acount_number'
        AND acount_id!='$ACCOUNT_ID'
    "));

    if($count>0){
        echo '<div class="bg-red-500 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded">
                <strong class="font-bold">Oops!</strong>
                <span class="block sm:inline">This Account '. $acount_number .' already exists.</span>
              </div>';
    } else {

        $update = mysqli_query($conn,"
            UPDATE bank_account SET
                acount_number='$acount_number',
                acount_status='$acount_status'
            WHERE acount_id='$ACCOUNT_ID'
        ");

        if($update){
            header('Location: Bank_account_numbers?BANK='.$BANK);
            echo '<div class="bg-green-300 mb-2 border px-4 py-3 rounded">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">Redirecting...</span>
                  </div>';
        } else {
            echo '<div class="bg-red-500 mb-2 border px-4 py-3 rounded">
                    <strong class="font-bold">Oops!</strong>
                    <span class="block sm:inline">Something went wrong. Try again.</span>
                  </div>';
        }
    }
}
?>
    <div class="mt-4">
        <center>
            <button type="submit" name="Update_account"
                    class="px-4 py-1 text-white font-light tracking-wider bg-blue-500 rounded">
                Update Account
            </button>
        </center>
    </div>

</form>

        </div>
    </div>
</div>

</body>
</html>
