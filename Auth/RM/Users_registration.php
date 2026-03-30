<?php include('header.php');?>
        <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
             <?php include('dynamic_side_bar.php');?>
            <!--/Sidebar-->
            <!--Main-->
            <!--Main-->
            <main class="bg-white-medium flex-1 p-3 overflow-hidden">
                <div class='flex flex-col'>
                    <div class='flex flex-1  flex-col md:flex-row lg:flex-row mx-2'>
                        <div class="mb-2 mx-2 border-solid border-gray-300 rounded border shadow-sm w-full md:w-1/2 lg:w-1/2">
                            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-300 border-b">
                                Invite User
                            </div>
                            <div class="p-3">
                                <button data-modal='toppedModal'
                                    class="modal-trigger bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                   Invite User
                                </button>
                            </div>

                        </div>

                        
                    </div>

                    <div class='flex flex-1  flex-col md:flex-row lg:flex-row mx-2'>
                        <div class="mb-2 mx-2 border-solid border-gray-300  rounded border shadow-sm w-full md:w-1/2 lg:w-1/2">
                            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-300 border-b">
                                New User Registration
                            </div>
                            <div class="p-3">
                                <button data-modal='centeredFormModal'
                                    class="modal-trigger bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Register New User
                                </button>
                            </div>

                        </div>

                       
                    </div>
                </div>
            </main>
            <!--/Main-->
        </div>

        <!--Footer-->
        <footer class="bg-gray-900 text-white p-2">
            <div class="flex flex-1 mx-auto">&copy; My Design</div>
        </footer>
        <!--/footer-->

    </div>

</div>


<!-- Topped Modal -->
<div id='toppedModal' class="modal-wrapper">
    <div class="overlay close-modal"></div>
    <div class="modal">
        <div class="modal-content shadow-lg p-5">
            <div class="border-b p-2 pb-3 pt-0 mb-4">
               <div class="flex justify-between items-center">
                    Invite User For Registration
                    <span class='close-modal cursor-pointer px-3 py-1 rounded-full bg-gray-100 hover:bg-gray-200'>
                        <i class="fas fa-times text-gray-700"></i>
                    </span>
               </div>
            </div>
            <!-- Modal content -->
              <form id='form_id' action="invite_user"  Method="POST" class="w-full">
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1" for="grid-first-name">
                            First Name
                        </label>
                        <input
                            class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white-500"
                            id="grid-first-name" type="text" name ="First_Name" placeholder="First Name">
                        <p class="text-red-500 text-xs italic">Please fill out this field.</p>
                    </div>
                    <div class="w-full md:w-1/2 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1" for="grid-last-name">
                            Phone No
                        </label>
                        <input
                            class="appearance-none block w-full bg-gray-200 text-grey-darker border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                            id="grid-last-name" type="text" name="phone_number" placeholder="Phone No">
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full px-3">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-password">
                            User Email
                        </label>
                        <input
                            class="appearance-none block w-full bg-grey-200 text-grey-darker border border-grey-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-grey"
                            id="grid-password" type="text" name ="User_Email" placeholder="User Email">
                        
                    </div>
                </div>
                

                <div class="mt-5">
                    <button name= "invite" type ="submit" class='bg-green-500 hover:bg-green-800 text-white font-bold py-2 px-4 rounded'>Invite User</button>
                    <span class='close-modal cursor-pointer bg-red-200 hover:bg-red-500 text-red-900 font-bold py-2 px-4 rounded'>
                        Close
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Centered Modal -->
<div id='centeredModal' class="modal-wrapper">
    <div class="overlay close-modal"></div>
    <div class="modal modal-centered">
        <div class="modal-content shadow-lg p-5">
            <div class="border-b p-2 pb-3 pt-0 mb-4">
               <div class="flex justify-between items-center">
                    Modal header
                    <span class='close-modal cursor-pointer px-3 py-1 rounded-full bg-gray-100 hover:bg-gray-200'>
                        <i class="fas fa-times text-gray-700"></i>
                    </span>
               </div>
            </div>
            <!-- Modal content -->
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Sint impedit placeat nulla accusamus tempora, error inventore, ducimus est soluta voluptatem eligendi, saepe ullam non ratione laboriosam itaque cumque? Eaque, excepturi.
            </p>
        </div>
    </div>
</div>

<!-- Centered With a Form Modal -->
<div id='centeredFormModal' class="modal-wrapper">
    <div class="overlay close-modal"></div>
    <div class="modal modal-centered">
        <div class="modal-content shadow-lg p-5">
            <div class="border-b p-2 pb-3 pt-0 mb-4">
               <div class="flex justify-between items-center">
                   Register New User
                    <span class='close-modal cursor-pointer px-3 py-1 rounded-full bg-gray-100 hover:bg-gray-200'>
                        <i class="fas fa-times text-gray-700"></i>
                    </span>
               </div>
            </div>
            <!-- Modal content -->
            <form id='form_id' class="w-full">
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1" for="grid-first-name">
                            First Name
                        </label>
                        <input
                            class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white-500"
                            id="grid-first-name" name="First_Name" type="text" placeholder="Jane">
                        <p class="text-red-500 text-xs italic">Please fill out this field.</p>
                    </div>
                    <div class="w-full md:w-1/2 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1" for="grid-last-name">
                            Last Name
                        </label>
                        <input
                            class="appearance-none block w-full bg-gray-200 text-grey-darker border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                            id="grid-last-name" name="Last_Name" type="text" placeholder="Doe">
                    </div>
					<div class="w-full md:w-1/2 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1" for="grid-last-name">
                            User Phone
                        </label>
                        <input
                            class="appearance-none block w-full bg-gray-200 text-grey-darker border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                            id="grid-last-name" name="User_Phone" type="text" placeholder="Doe">
                    </div>
					<div class="w-full md:w-1/2 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1" for="grid-last-name">
                            User Email
                        </label>
                        <input
                            class="appearance-none block w-full bg-gray-200 text-grey-darker border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                            id="grid-last-name" name="User_Email" type="text" placeholder="Doe">
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full px-3">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-password">
                            Password
                        </label>
                        <input
                            class="appearance-none block w-full bg-grey-200 text-grey-darker border border-grey-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-grey"
                            id="grid-password" name ="Password" type="password" placeholder="******************">
                        <p class="text-grey-dark text-xs italic">Make it as long and as crazy as
                            you'd like</p>
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3 mb-2">
                    
					 <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           User Role
                        </label>
                        <div class="relative">
                            <select name="User_Status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
                                <option value="Active">Active</option>
                                <option value="Burned">Burned</option>
                                <option value="Suspended">Suspended</option>
								
                                <option value="Deleted">Deleted</option>
                            </select>
                             
                        </div>
                    </div>
					 <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           User School
                        </label>
                        <div class="relative">
                            <select name="User_Status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
                                <option value="Active">==school==</option>
								<?php
								$select_chool= mysqli_query($conn,"SELECT * FROM schools");
								while($find_school = mysqli_fetch_array($select_chool)){
								?><option value="<?php echo $find_school['school_id']; ?>"><?php echo $find_school['school_name']; ?></option><?php	
								}
								?>
                               
                                 
                            </select>
                             
                        </div>
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="grid-state">
                           User Status
                        </label>
                        <div class="relative">
                            <select name="User_Status"
                                class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                id="grid-state">
                                <option value="Active">Active</option>
								<option value="Inactive">Inactive</option>
                                <option value="Burned">Burned</option>
                                <option value="Suspended">Suspended</option>
								
                                <option value="Deleted">Deleted</option>
                            </select>
                             
                        </div>
                    </div>
                    
                </div>

                <div class="mt-5">
                    <button class='bg-green-500 hover:bg-green-800 text-white font-bold py-2 px-4 rounded'> Submit </button>
                    <span class='close-modal cursor-pointer bg-red-200 hover:bg-red-500 text-red-900 font-bold py-2 px-4 rounded'>
                        Close
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- large Size Modal -->
<div id='largeSizeModal' class="modal-wrapper">
    <div class="overlay close-modal"></div>
    <div class="modal modal-lg">
        <div class="modal-content shadow-lg p-5">
            <div class="border-b p-2 pb-3 pt-0 mb-4">
               <div class="flex justify-between items-center">
                    Modal header
                    <span class='close-modal cursor-pointer px-3 py-1 rounded-full bg-gray-100 hover:bg-gray-200'>
                        <i class="fas fa-times text-gray-700"></i>
                    </span>
               </div>
            </div>
            <!-- Modal content -->
           <p>
               Lorem ipsum dolor sit amet consectetur adipisicing elit. Earum vero amet minus facere, vitae quas suscipit harum aspernatur sint non rerum deleniti explicabo excepturi cumque nihil neque in. Consectetur
               Lorem ipsum, dolor sit amet consectetur adipisicing elit. Corporis repellendus, excepturi saepe aliquam reprehenderit fugit quam non dignissimos voluptatem sed numquam ex aut earum tempore beatae delectus itaque asperiores neque.
           </p>
        </div>
    </div>
</div>



<script src="../../main.js"></script>

</body>

</html>