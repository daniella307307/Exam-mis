<?php
include('header.php');
?>
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
                                    Ongoing courses
                                </a>
								<a href="#" class="no-underline text-white text-2xl">
                                    0
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-info border-l-8 hover:bg-info-dark border-info-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                    Completed Courses
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    0
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-warning border-l-8 hover:bg-warning-dark border-warning-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                   Erned Certificates
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    0
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-success border-l-8 hover:bg-success-dark border-success-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                   Current Certification
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Platinum coders certification/2024
                                </a>
                            </div>
                        </div>
                    </div>
 
             
                </div>
				<div class="flex flex-col">
                    <!-- Stats Row Starts Here -->
                     
 
             
                </div>
				 
            </main>
            <!--/Main-->
        </div>
        <?php include('footer.php'); ?>

    </div>

</div>
<script src="../../main.js"></script>
</body>

</html>