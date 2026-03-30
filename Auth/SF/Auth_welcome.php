<?php include('header.php');?>
        <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
             <?php include('dynamic_side_bar.php');?>
            <!--/Sidebar-->
            <!--Main-->
            <main class="bg-white-300 flex-1 p-3 overflow-hidden">

                <div class="flex flex-col">
                    <!-- Stats Row Starts Here -->
                    <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
					
                        <div class="shadow-lg bg-red-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                
                                <a href="#" class="no-underline text-white text-lg">
                                    Number of Students
                                </a>
								<a href="#" class="no-underline text-white text-2xl">
                                     <?php echo $Available_students;?>  <i class="fa fa-users float-left mx-2"></i>
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-info border-l-8 hover:bg-info-dark border-info-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                   Classes in school
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                  <?php echo number_format( 6,0);?>  <i class="fa fa-home float-left mx-2"></i> Classes
                                    
                                    
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-warning border-l-8 hover:bg-warning-dark border-warning-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                              # Facilitators
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    <?php echo number_format(2,0) ;?> Facilitators <i class="fa fa-users float-left mx-2"></i>
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-success border-l-8 hover:bg-success-dark border-success-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                Programs
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                   <?php echo number_format( 1,0) ;?>  <i class="fas fa-book-open mr-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
 
             
                </div>
				 
            </main>
            <!--/Main-->
        </div>
        <!--Footer-->
        <?php include('footer.php');?>
        <!--/footer-->

    </div>

</div>
<script src="../../main.js"></script>
</body>

</html>