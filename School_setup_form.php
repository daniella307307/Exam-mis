<?php
ob_start();
include('header.php');
 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update School Details</title>
</head>
<body class="h-screen font-sans login bg-cover">

<!--/Header-->
<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->
    
    <!--Main-->
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                   <div class="flex items-center mb-2 bg-yellow-500 text-white text-sm font-bold px-4 py-3" role="alert"> 
                   <p>Warning: This form is under construction.</p> 

                  </center></strong> </div>  
                    
                    
                  <div class="flex items-center mb-2 bg-red-500 text-white text-sm font-bold px-4 py-3" role="alert"> 
                  <strong><center> <u><h2>warning</h2></u>
                  <p>This form is intended to collect the number of students in each class, helping our engineers set up the precise lab tools and equipment needed to create a smooth learning environment for the Coding and Robotics ICRPlus Program. BLIS Global Group is partnering with schools to support this initiative.</p>

<p>NB:Providing incorrect information may result in inaccurate services from BLIS, affecting the quality of support in your Coding and Robotics journey. </p>
<p></p>
<p>This form Should be fielled by only School Mangers or School Owner</p>

                  </center></strong> </div>  
                    
                    
                    
                    
                    
           <p class="text-gray-800 font-medium">Add your School Details <strong> </strong> </p>
                     <?php
				 
					?>
					
					 <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="access_level">School Name</label>
                            <div class="relative">
                                 <select name="student_class" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="school_ref">
                                     <option value="">====Select School====</option>
                                    <?php
                                    $select_school = mysqli_query($conn, "SELECT * FROM schools WHERE school_categoty!='PLay Ground'  AND country_ref='118'");
                                    while ($find_school = mysqli_fetch_array($select_school)) {
                                        echo '<option value="' . $find_school['school_id'] . '">' . $find_school['school_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
					
				  <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">School Owner</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="student_contact"  type="text" placeholder=" Owner First and Last Names" required >
                    </div> 
                     <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">School Owner Contact</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="student_contact"  type="text" placeholder=" Owner Phone Number" required>
                    </div> 
                     <div class="">
                        <label class="block text-sm text-gray-600" for="lastname">School Owner Email</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="lastname" name="student_contact"  placeholder=" Owner Phone Email" type="text" required>
                    </div> 
                   
                    
                    
                     <center><p class="text-green-800 font-medium">Record Number of Students In each Category<strong> </strong> </p></center>
                    
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">1)PLAYGROUND </label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">2)PP 1 </label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">3)PP 2 </label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                    
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">4)GRADE 1</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">5)GRADE 2</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">6)GRADE 3</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">7)GRADE 4</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">8)GRADE 5</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">9)GRADE 6</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">10)GRADE 7</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">11)GRADE 8</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">12)GRADE 9</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">13)GRADE 10</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">14)GRADE 11</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                    <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">15)GRADE 12</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
                     <div class=""> 
                        <label class="block text-sm text-gray-600" for="firstname">16)NEW TECHNOLOGIES INCUBATION CENTER (NITIC)</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="student_first_name" name="PLAYGROUND"  value ="0" min = "0" type="number" required >
                    </div>
					<div class=""> 
                            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="status">Information Status</label>
                            <div class="relative">
                                <select name="student_status" class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" id="status">
                                    <option value="">My Information is not Accurate</option>
                                     <option value="Accurate">My Information is Accurate</option>
                                      
                                    
                                </select>
                            </div>
                        </div>
                     
 

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 text-white font-light tracking-wider bg-green-500 rounded">Save my information; I'm sure  it is true and  accurate.</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
ob_end_flush();
?>
