<aside id="sidebar" class="bg-side-nav w-1/2 md:w-1/6 lg:w-1/6 border-r border-side-nav hidden md:block lg:block">
                <div class="flex">

                </div>
                <ul class="list-reset flex flex-col">
                    <li class=" w-full h-full py-3 px-2 border-b border-light-border ">
                        <a href="index"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                             <img class="inline-block h-12 w-12 rounded-full" src="../<?php echo $user_data['Country_flag'];?>" alt="">
                           
                            <?php echo $user_data['Country_name'];?> 
                        </a>
                    </li>
                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="index"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-home float-left mx-2"></i>
                           Home
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    
                    
					<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Robotics_Material_List"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-robot float-left mx-2"></i>
                            Robotics Material List
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Robotics_Categories_List"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-robot float-left mx-2"></i>
                            Categories
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                   <!--
                   <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Robotics_Sub_Categories_List"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-robot float-left mx-2"></i>
                            Subcategories
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    
                    
					<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Regions"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-map-marker-alt float-left mx-2"></i>
                            Regions
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
					<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Countries"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-map-marker-alt float-left mx-2"></i>
                            Countries
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
					 <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Courses_settings"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-book float-left mx-2"></i>
                            Courses Settings
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    
                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Bunny_Settings"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-book float-left mx-2"></i>
                            File Storage Settings
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
                    

                    
                    
                    
					<li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Roles_per_User"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-wrench float-left mx-2"></i>
                            Your Roles
                            <span><i class="fa fa-angle-right float-right"></i></span>
                        </a>
                    </li>
					 <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="User_profile"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fas fa-wrench float-left mx-2"></i>
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
                    <li class="w-full h-full py-3 px-2 border-b border-light-border">
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