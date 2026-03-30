<?php
ob_start();
include('header.php');
$current_page = basename($_SERVER['PHP_SELF']);
$COUNTRY =$_GET['COUNTRY']; 
if(isset($_GET['STATUS'])){
$STATUS= $_GET['STATUS'];
if($STATUS=="Inactive"){
$action = "(bank_status='$STATUS' OR bank_status !='Active')";	
}
else{
	$action = "bank_status='$STATUS'";
}

}
else{
$STATUS= "Active";
$action = "bank_status ='$STATUS'";	
} 
$country_details = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM countries 
LEFT JOIN regions_table ON  countries.Country_region = regions_table.region_id WHERE id='$COUNTRY'"));
$select_bank = mysqli_query($conn, "SELECT * FROM banks 
LEFT JOIN countries on banks.bank_country = countries.id
LEFT JOIN regions_table on banks.bank_region = regions_table.region_id WHERE bank_country='$COUNTRY' AND $action");
$inc=mysqli_num_rows($select_bank);

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
                        
                        <a href="Banks_in_Countries?STATUS=Active&COUNTRY=<?php echo $COUNTRY; ?>"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                        <a href="Banks_in_Countries?STATUS=Inactive&COUNTRY=<?php echo $COUNTRY;?>"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>
                        <?php
                        if($STATUS=="Active"){}else{
                        ?> <a href="Add_New_Bank?COUNTRY=<?php echo $COUNTRY; ?>&CALL_BACK=<?php echo $current_page; ?>"><button class='bg-yellow-400 hover:bg-blue-400 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus"></i>&nbsp;&nbsp;Add New</button></a><?php
                        }
                        ?>
                       
                        
                        
                           </div>
                    <div class="p-3">
					<center><big> <strong><?PHP echo $STATUS;?></strong>&nbsp; Banks located in <strong><?PHP echo $country_details['Country_name'];?></strong></big></center><br>
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">#</th>
                                    <th class="border w-1/8 px-4 py-2">Bank Name</th>
									 <th class="border w-1/8 px-4 py-2">Country</th>
                                    <th class="border w-1/8 px-4 py-2">Region</th> 
                                     <th class="border w-1/8 px-4 py-2">Account List</th> 
									<th class="border w-1/9 px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
 <?php while($bank_details =mysqli_fetch_array($select_bank)) {
	        $bank_id= $bank_details['bank_id'];
            $bank_status= $bank_details['bank_status'];	
if($bank_status=="Active"){
	$link ="Update_bank_status?COUNTRY=$COUNTRY&BANK=$bank_id&CURRENT=$bank_status&STATUS=Inactive";
	$bG="bg-red-500";
	$icon="fa-lock";
}
else{
	$link ="Update_bank_status?COUNTRY=$COUNTRY&BANK=$bank_id&CURRENT=$bank_status&STATUS=Active";
	$bG="bg-green-500";
	$icon="fa-unlock";
}			
								?>
                                    <tr>
									    
                                        <td class="border px-4 py-2"><?php echo $bank_details['bank_id']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $bank_details['bank_name']; ?></td> 
                                        <td class="border px-4 py-2"><?php echo $bank_details['Country_name']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $bank_details['region_name']; ?></td> 
                                          <td class="border px-4 py-2"> <a href="Accounts_in_Banks?Bank=<?php echo $bank_details['bank_id']; ?>&COUNTRY=<?php echo $COUNTRY; ?>"><button class='bg-green-400 hover:bg-blue-400 text-white font-bold py-2 px-4 rounded'><i class="fas fa-school"></i>&nbsp;&nbsp;Acounts List</button></a></td> 
                                        <td class="border px-4 py-2"> <a href="<?php echo $link;?>" class="<?php echo $bG; ?> cursor-pointer rounded p-1 mx-1 text-white">&nbsp;<i class="fas <?php echo $icon; ?>"></i></a>
                                      
                                       </td>
                                    </tr>
                                <?php } ?>
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
