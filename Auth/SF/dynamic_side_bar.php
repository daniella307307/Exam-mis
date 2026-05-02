<?php require_once __DIR__ . '/../../app_config.php'; ?>
<aside id="sidebar" class="bg-side-nav w-1/2 md:w-1/6 lg:w-1/6 border-r border-side-nav hidden md:block lg:block">
                <div class="flex">

                </div>
                <ul class="list-reset flex flex-col">
                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="<?= APP_BASE_URL ?>/exams/live_classes.php"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-video float-left mx-2"></i>
                            &nbsp;&nbsp; Join Online Class
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    <!-- <li class=" w-full h-full py-3 px-2 border-b border-light-border ">
                        <a href="index"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                             <img class="inline-block h-12 w-12 rounded-full" src="../<?php echo $user_data['Country_flag'];?>" alt="">
                           
                            <?php echo $user_data['Country_name'];?> 
                        </a>
                    </li> -->
                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Laboratory_Equipments"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-wrench float-left mx-2"></i>
                            &nbsp;&nbsp; Laboratory Equipments
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a> 
                    </li>
                    	<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Level_Classes"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-home float-left"></i>
                            &nbsp;&nbsp; Level/ Classes
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a> 
                    </li>
				 
					<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="School_class_rooms"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-home float-left"></i>
                            &nbsp;&nbsp; School Classes
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a> 
                    </li>

                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Students_perschool"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-users float-left mx-2"></i>
                            Students List
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    <?php
// Detect environment automatically
if ($_SERVER['SERVER_NAME'] === 'localhost' && is_dir('/var/www/html/Exam-mis')) {
    // Ubuntu/Linux local
    $base_url = "/Exam-mis";
} elseif ($_SERVER['SERVER_NAME'] === 'localhost') {
    // Windows XAMPP local
    $base_url = "/_bluelackesadigital.com/public_html";
} else {
    // Live production server
    $base_url = "";
}
?>
                      <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="<?php echo $base_url; ?>/exams/exams_library.php"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-users float-left mx-2"></i>
                            Exams Library
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                     <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Current_Courses"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-book float-left mx-2"></i>
                            Current Courses
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>

                    <!-- EXAM DASHBOARDS SECTION -->
                    <li class="w-full h-full py-3 px-2 border-b border-light-border" style="background-color: #f0f4f8; font-weight: bold;">
                        <i class="fas fa-chart-bar float-left mx-2" style="color: #667eea;"></i>
                        &nbsp;&nbsp; Exam Dashboards
                    </li>

                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="<?php echo $base_url; ?>/exams/index-router.php"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-tachometer-alt float-left mx-2" style="color: #667eea;"></i>
                            My Dashboard
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>

                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="<?php echo $base_url; ?>/exams/teacher/dashboard-integrated.php"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-chalkboard-user float-left mx-2" style="color: #ffc107;"></i>
                            Teacher Analytics
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>

                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="<?php echo $base_url; ?>/exams/admin/records-integrated.php"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-cogs float-left mx-2" style="color: #dc3545;"></i>
                            System Analytics
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
					
					
					  
					<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Roles_per_User"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                           
                            <i class="fas fa-sync float-left"></i>&nbsp;
                            Your Access Levels
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    	<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Users_School_allocations"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-school float-left mx-2"></i>
                            Your Allocation
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li> 
					<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="User_profile"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-user float-left mx-2"></i>
                            Your Profile
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li> 
					<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Logout"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-lock float-left mx-2"></i>
                            Logout
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    <!--<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="buttons.html"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-grip-horizontal float-left mx-2"></i>
                            Buttons
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    <li class="w-full h-full py-3 px-2 border-b border-light-border bg-white">
                        <a href="tables.html"
                            class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-table float-left mx-2"></i>
                            Tables
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="ui.html"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fab fa-uikit float-left mx-2"></i>
                            Ui components
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    <li class="w-full h-full py-3 px-2 border-b border-300-border">
                        <a href="modals.html" class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-square-full float-left mx-2"></i>
                            Modals
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    <li class="w-full h-full py-3 px-2">
                        <a href="#"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="far fa-file float-left mx-2"></i>
                            Pages
                            <span><i class="fa fa-angle-down float-right"></i></span>
                        </a>
                        <ul class="list-reset -mx-2 bg-white-medium-dark">
                            <li class="border-t mt-2 border-light-border w-full h-full px-2 py-3">
                                <a href="login.html"
                                   class="mx-4 font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    Login Page
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            <li class="border-t border-light-border w-full h-full px-2 py-3">
                                <a href="register.html"
                                   class="mx-4 font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    Register Page
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            <li class="border-t border-light-border w-full h-full px-2 py-3">
                                <a href="404.html"
                                   class="mx-4 font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    404 Page
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>-->
                        </ul>
                    </li>
                </ul>

            </aside>