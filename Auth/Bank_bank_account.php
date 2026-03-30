<!DOCTYPE html>
<?php 
include('../db.php');


?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank to Bank Account Dropdown</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>Select Your Bank and Account  <?php echo  $message;?></h2>
    
    <label for="bank">Bank:</label>
    <select id="bank" name="bank">
        <option value="">Select a Bank</option>
        <!-- Example static options. These should be generated dynamically from your database -->
        <?php
       $select_bank = mysqli_query($conn,"SELECT * FROM banks  WHERE bank_status='Active'"); 
       while($find_bank =  mysqli_fetch_array($select_bank)){
  ?> <option value="<?php echo $find_bank['bank_id'];?>"> <?php echo $find_bank['bank_name'];?> </option><?php
       }
        
        
        ?>
    </select>

    <label for="bank_account">Bank Account: <?php echo $bank_id;?></label>
    <select id="bank_account" name="bank_account">
        <option value="">Select a Bank Account</option>
        <!-- This will be populated based on the bank selection -->
       
    </select>
    <br>
     <br>
      <br>

    <script>
        $(document).ready(function() {
            $('#bank').change(function() {
                var bankId = $(this).val();
                $('#bank_account').empty(); // Clear previous options
                $('#bank_account').append('<option value="">Select a Bank Account 1234</option>');
                
                if (bankId) {
                    // Here we use AJAX to fetch the bank accounts for the selected bank
                    $.ajax({
                        url: 'get_bank_accounts.php', // URL to the server-side script that fetches bank accounts
                        type: 'GET',
                        data: { bank_id: bankId },
                        success: function(data) {
                            var accounts = JSON.parse(data);
                            $.each(accounts, function(key, value) {
                                $('#bank_account').append('<option value="' + key + '">' + value + '</option>');
                            });
                        },
                        error: function() {
                            alert('Error fetching bank accounts.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
