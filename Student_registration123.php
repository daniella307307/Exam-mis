<?php include('header.php'); ?>
<!--/Header-->
<div class="flex flex-1"> 
    <!--Main-->
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-4xl"> <!-- Increased max width -->
            <div class="leading-loose">	
                <!-- Removed max-w-xl and m-12, increased padding -->
                <form action="" method="POST" class="w-full p-10 bg-green-300 rounded shadow-xl">
                    <center>
                        
                        <h3 class="text-gray-800 font-medium text-xl mb-12"> <!-- Larger text -->
                            <strong>Register New Student </strong>
                        </h3>
                    </center>
                    
                    
                    <?php
                    if (isset($_POST['register'])) {
                    $student_first_name= $_POST['student_first_name'];
                    $student_midle_name = $_POST['student_midle_name'];
                    $student_last_name= $_POST['student_last_name'];
                    $student_dob= $_POST['student_dob'];
                    $student_nationality= $_POST['student_nationality'];
                    $student_gender= $_POST['student_gender'];
                    $student_class = $_POST['student_class'];
                    
                    /*
                    
                    school_id
                    school_name
                    country_ref
                    school_region
                    */
                    
                    }
                    
                    
                    ?>
                                       
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> <!-- Grid layout -->
                        <!-- First Column -->
                        <div class="bg-blue-500 mb-2 border border-red-300 text-white px-12 py-3 rounded relative" role="alert">
                                    <center><strong class="font-bold">Student Details</strong></center> </div>
                        
                        <div class="space-y-6">
                             <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
    <div>
        <label class="block text-sm text-gray-600 mb-1" for="student_first_name">First Name</label>
        <input class="w-full  px-3 py-2 text-gray-700 bg-blue-200 rounded"
               id="student_first_name" name="student_first_name" type="text" required>
    </div>

    <div>
        <label class="block text-sm text-gray-600 mb-1" for="student_midle_name">Middle Name</label>
        <input class="w-full px-3 py-2 text-gray-700 bg-blue-200 rounded"
               id="student_midle_name" name="student_midle_name" type="text" required>
    </div>

    <div>
        <label class="block text-sm text-gray-600 mb-1" for="student_last_name">Last Name</label>
        <input class="w-full px-3 py-2 text-gray-700 bg-blue-200 rounded"
               id="student_last_name" name="student_last_name" type="text" required>
    </div>
</div>


 

<!--


                            <div>
                                <label class="block text-sm text-gray-600 mb-1" for="student_dob">Both Date</label>
                                <input class="w-2/3 px-4 py-2 text-gray-700 bg-blue-200 rounded" 
                                       id="student_dob" name="student_dob" type="date" required>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1" for="student_nationality">Nationality</label>
                                <input class="w-2/3 px-4 py-2 text-gray-700 bg-blue-200 rounded" 
                                       id="date student_nationality" name="date student_nationality" type="text" required>
                            </div>
                             <div>
                                <label class="block text-sm text-gray-600 mb-1" for="date student_nationality">Gender</label>
                                 <select name="student_gender" 
                                class="block w-full bg-grey-200 border border-grey-200 text-grey-darker py-2 px-4 rounded focus:outline-none focus:bg-white focus:border-grey" 
                                id="status" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                            </div>
                             <div>
                                <label class="block text-sm text-gray-600 mb-1" for="student_status">School Name</label>
                                 <select name="COUNTRY" 
                                class="block w-full bg-grey-200 border border-grey-200 text-grey-darker py-2 px-4 rounded focus:outline-none focus:bg-white focus:border-grey" 
                                id="school_ref" required>
                            <?php
                            $select_country123 = mysqli_query($conn, "SELECT * FROM schools WHERE  country_ref='186'AND school_status='Active'");
                            while ($find_country = mysqli_fetch_array($select_country123)) {
                                echo '<option value="' . $find_country['school_id'] . '">' . $find_country['school_name'] . '</option>';
                            }  
                            ?>
                        </select>
                            </div>   
                    
                             <div>
                                <label class="block text-sm text-gray-600 mb-1" for="student_status">Registration Status</label>
                                 <select name="student_status" 
                                class="block w-full bg-grey-200 border border-grey-200 text-grey-darker py-2 px-4 rounded focus:outline-none focus:bg-white focus:border-grey" 
                                id="status" required>
                            <option value="Inprogress">Inprogress</option> 
                        </select>
                            </div>
                            -->
              
                    <div class="mt-8 text-center">
                        <button type="submit" name="register" 
                                class="px-6 py-2 text-white font-medium tracking-wider bg-green-600 hover:bg-green-700 rounded transition duration-200">
                            Registre Student and Create your Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<?php ob_end_flush(); ?>