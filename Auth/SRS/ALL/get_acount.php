<?php
require_once("../../db.php");
if(!empty($_POST["coutrycode"])) 
{
$query =mysqli_query($conn,"SELECT * FROM bank_account WHERE acount_bank= '" . $_POST["coutrycode"] . "'");
?>
<option value=""> === SELECT ACCOUNT ====</option>
<?php
while($row=mysqli_fetch_array($query))  
{
?>
<option value="<?php echo $row["acount_id"]; ?>"><?php echo $row["acount_number"]; ?></option>
<?php
}
}
 


?>
