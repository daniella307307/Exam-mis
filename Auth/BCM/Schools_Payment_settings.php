<?php include('header.php');?>
        <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
             <?php include('payment_side_bar.php');
			 include('statistics_counter.php');
			 ?>
            <!--/Sidebar-->
            <!--Main-->
            <main class="bg-white-300 flex-1 p-3 overflow-hidden">

                <div class="flex flex-col">
                    <!-- Stats Row Starts Here -->
                    <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                        <div class="shadow-lg bg-red-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                
                                <a href="#" class="no-underline text-white text-lg">
                                    
 <?php echo $regions;?>&nbsp; <i class="fas fas fa-globe text-white-500 mx-6"></i>  
                               		 							
                                </a>
								<a href="#" class="no-underline text-white text-2xl">
                                    Active Region<?php if($regions>=2){echo "s";}else{echo "";}?></a>
                            </div>
                        </div>

                        <div class="shadow bg-info border-l-8 hover:bg-info-dark border-info-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                    <?php echo $countries;?> <i class="fas  fas fa-flag text-white-500 text-2xl"></i>
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Active Countr<?php if($countries>=2){echo "ies";}else{echo "y";}?>
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-warning border-l-8 hover:bg-warning-dark border-warning-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                    
									<?php echo $schools;?> <i class="fas fa-school text-white-500 text-2xl"></i>
                                
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Active School<?php if($countries>=2){echo "s";}else{echo "";}?>
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-success border-l-8 hover:bg-success-dark border-success-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                   <?php echo $Students;?>&nbsp; <i class="fas fa-user<?php if($Students>=2){echo "s";}else{echo "";}?> text-white-500 text-2xl"></i>
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Active Student<?php if($Students>=2){echo "s";}else{echo "";}?>
                                </a>
                            </div>
                        </div>
                    </div>
 
             
                </div>
				
				 <!--OKOOKOK -->
				 <div class="flex flex-col">
                    <!-- Stats Row Starts Here -->
                    <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                        <div class="shadow-lg bg-red-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                
                                <a href="#" class="no-underline text-white text-lg">
                                    
 <?php echo $regions;?>&nbsp; <i class="fas fa-dollar-sign text-white-500 mx-6"></i>  
                               		 							
                                </a>
								<a href="#" class="no-underline text-white text-2xl">
                                    Expected Income</a>
                            </div>
                        </div>

                        <div class="shadow bg-info border-l-8 hover:bg-info-dark border-info-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                    <?php echo $countries;?> <i class="fas fa-dollar-sign text-white-500 mx-6"></i>
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                   Collected amount   </a>
                            </div>
                        </div>

                        <div class="shadow bg-warning border-l-8 hover:bg-warning-dark border-warning-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                    
									<?php echo $schools;?> <i class="fas fa-school text-white-500 text-2xl"></i>
                                
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Active School<?php if($countries>=2){echo "s";}else{echo "";}?>
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-success border-l-8 hover:bg-success-dark border-success-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                   <?php echo $Students;?>&nbsp; <i class="fas fa-user<?php if($Students>=2){echo "s";}else{echo "";}?> text-white-500 text-2xl"></i>
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Active Student<?php if($Students>=2){echo "s";}else{echo "";}?>
                                </a>
                            </div>
                        </div>
                    </div>
 
             
                </div>
				<!--<div class="flex flex-col">
                   
				   
                    <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                        <div class="shadow-lg bg-red-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                    $244
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Total Sales
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-info border-l-8 hover:bg-info-dark border-info-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                    $199.4
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Total Cost
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-warning border-l-8 hover:bg-warning-dark border-warning-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                    900
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Total Users
                                </a>
                            </div>
                        </div>

                        <div class="shadow bg-success border-l-8 hover:bg-success-dark border-success-dark mb-2 p-2 md:w-1/4 mx-2">
                            <div class="p-4 flex flex-col">
                                <a href="#" class="no-underline text-white text-2xl">
                                    500
                                </a>
                                <a href="#" class="no-underline text-white text-lg">
                                    Total Products
                                </a>
                            </div>
                        </div>
                    </div>
 
             
                </div>-->
            </main>
            <!--/Main-->
        </div>
        <!--Footer-->
        <?php include('footer.php')?>
        <!--/footer-->

    </div>

</div>
<script src="../../main.js"></script>
</body>

</html>