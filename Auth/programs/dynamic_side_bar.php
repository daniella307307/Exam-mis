<aside id="sidebar" class="bg-side-nav w-1/2 md:w-1/6 lg:w-1/6 border-r border-side-nav hidden md:block lg:block">
                <div class="flex">

                </div>
                <ul class="list-reset flex flex-col">
                     
					<li class=" w-full h-full py-3 px-2 border-b border-light-border ">
                        <a href="index"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                             <img class="inline-block h-12 w-12 rounded-full" src="../<?php echo  $school_details['Country_flag'];?>" alt="">
                           
                            <?php echo  $school_details['Country_name'];?> 
                        </a>
                    </li>
					 <li class="w-full h-full py-3 px-2 border-b border-light-border">
                        <a href="Current_Program_Details"
                           class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                            <i class="fa fa-book float-left mx-2"></i>
                           Current Program Details
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
                   
                        </ul>
                    </li>
                </ul>

            </aside>