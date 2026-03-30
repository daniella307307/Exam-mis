<?php
ob_start();
include('header.php');
if(isset($_GET['STATUS'])){
   $STATUS =$_GET['STATUS'];
}
else{
  $STATUS ="Active";   
}

$country_details = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM countries
LEFT JOIN regions_table ON countries.Country_region = regions_table.region_id WHERE id='$user_country'"));
$select_bank = mysqli_query($conn, "SELECT * FROM banks  WHERE bank_country='$user_country' AND bank_status='$STATUS'");
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php');?>
    <!--/Sidebar-->
    <!--Main-->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <!-- Card Section Starts Here -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <!--Horizontal form-->
                <!--/Horizontal form-->
                <!--Underline form-->
                <!--/Underline form-->
            </div>
            <!-- /Cards Section Ends Here -->

            <!--Grid Form-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                        
                        <a href="Banks.php?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                        <a href="Banks.php?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>
                        <a href="Add_New_Student"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus"></i>&nbsp;&nbsp;Add</button></a> 
                        <a href="export_students.php?format=excel"><button class='bg-blue-800 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-file-excel"></i>&nbsp;&nbsp;Excel</button></a>
                    </div>
                    <div class="p-3">
					<center><big>Banks located in <strong><?PHP echo $country_details['Country_name']." in ".$country_details['region_name'];?></strong></big></center><br>
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">#</th>
                                    <th class="border w-1/8 px-4 py-2">Bank Name</th> 
                                    <th class="border w-1/8 px-4 py-2">Accounts</th>
                                    <th class="border w-1/9 px-4 py-2">Status</th>
									<th class="border w-1/9 px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
 <?php
 $inc=mysqli_num_rows($select_bank);
					if($inc>0){
 while($find_banks = mysqli_fetch_array($select_bank)) {
	  
				 
								?>
                                    <tr>
 
                                         <td class="border px-4 py-2"><?php echo $find_banks['bank_id']; ?></td> 
                                        <td class="border px-4 py-2"><?php echo $find_banks['bank_name']; ?></td>
                                         <td class="border px-4 py-2"><a href="Bank_account_numbers?BANK=<?php echo $find_banks['bank_id']; ?>"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Accounts</button></a></td>
                                        <td class="border px-4 py-2"><?php echo $find_banks['bank_status']; ?></td> 
                                        <td class="border px-4 py-2">
                                            <?php 
                                            if($find_banks['bank_status']=="Active"){
?><a href="Update_bank_status?BANK=<?php echo $find_banks['bank_id']; ?>&STATUS=Inactive"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fa-solid fas fa-sync"></i></button></a>
                                            <?php
                                                
                                            }
                                            else{
?><a href="Update_bank_status?BANK=<?php echo $find_banks['bank_id']; ?>&STATUS=Active"><button class='bg-red-500 hover:bg-red-800 text-white font-bold py-2 px-4 rounded'><i class="fa-solid fas fa-sync"></i></button></a>
                                            <?php                                                
                                            }
                                            
                                            
                                            ?>
                                          
                                        </td>
                                    </tr>
                                <?php } 
                                }else{
                                }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--/Grid Form-->
        </div>
        
        <!-- Pagination -->
        
    </main>
    <!--/Main-->
</div>

<!--Footer-->
<?php include('footer.php')?>
<!--/footer-->

<script src="../../main.js"></script>

</body>
</html>
