<?php 
include('header.php');
 include('Access.php');
 $CERTIFICATE =$_GET['CERTIFICATE'];
 $COURSE =$_GET['COURSE']; 
 $ID =$_GET['ID'];
 $selct_cert = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM certification_courses
LEFT JOIN certifications ON certification_courses.course_certificate = certifications.certification_id WHERE course_id ='$COURSE'"));
 $course_name =$selct_cert['course_name']; 
 $certification_name = $selct_cert['certification_name'];
 
 $select_file = mysqli_query($conn,"SELECT * FROM learning_topics WHERE topic_id='$ID'");
$File_found = mysqli_fetch_array($select_file);
$filePath= $File_found['topic_document'];
 
 
 if(isset($_GET['STATUS'])){
	 $STATUS =$_GET['STATUS'];
	 if($STATUS=="Active"){
		$status ="Active"; 
	 }
	 else{
		$status ="Inactive";  
	 }
	$action = "topic_course='$COURSE' AND topic_status ='$status'"; 
 }
 else{
$STATUS ="Active";	
$status = $STATUS; 
$action = "topic_course='$COURSE' AND topic_status ='$STATUS'";
 }
 $select_modules = mysqli_query($conn,"SELECT * FROM learning_topics 
LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id WHERE  $action");
 ?>
  
   <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.min.js"></script>
    <style>
   

        #pdfContainer {
            width: 150%; /* Adjust width as necessary */
            max-width: 900px;
            background-color: #f4f4f4;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .pdf-page {
             width: 100%;
            margin-bottom: 40px; /* Space between pages */
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .page-separator {
            height: 2px;
            background-color: #ccc;
            margin: 20px 0;
        }

        /* Ensure iframe is disabled */
        iframe {
            display: none;
        }
    </style>
  
  
  
        <!--/Header-->

        <div class="flex flex-1">
            <!--Sidebar-->
           <?php include('side_bar_courses.php');?>
            <!--/Sidebar-->
            <!--Main-->
            <main class="bg-white-500 flex-1 p-3 overflow-hidden">

                <div class="flex flex-col">
                    <!-- Card Sextion Starts Here -->
                    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                        <!--Horizontal form-->
                        
                        <!--/Horizontal form-->

                        <!--Underline form-->
                         
                        <!--/Underline form-->
                    </div>
                    <!-- /Cards Section Ends Here -->

                    <!--Grid Form-->

                    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                        <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                     	<a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE;?>"><button class='bg-blue-400 hover:bg-green-300 text-white font-bold py-2 px-4 rounded'>Back</button></a>  
                    
                           
							
							</div>
                            <div class="p-3">
							<p><strong><big>Main  Topics of the course </big></strong></p> 
							 
							 <p>Course Name:&nbsp;<strong><big><?php echo $course_name ;?></big></strong></p>
							 <p>Certificate Name:&nbsp;<strong><big><?php echo $certification_name ;?></big></strong></p> <br> 
							<p>Status : &nbsp;<strong><big><?php echo $status;?></big></strong></p> <br> 
					 
				 <div id="pdfContainer"></div>
    <script>
        var url = '<?php echo $filePath;?>';

        var pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.worker.min.js';

        var pdfContainer = document.getElementById('pdfContainer');

        var loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(function(pdf) {
            // Loop through each page
            for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                pdf.getPage(pageNumber).then(function(page) {
                    var scale = 1.5;
                    var viewport = page.getViewport({scale: scale});

                    // Create a canvas element for each page
                    var canvas = document.createElement('canvas');
                    canvas.className = 'pdf-page';
                    pdfContainer.appendChild(canvas);

                    var context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    // Render PDF page into canvas context
                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext);

                    // Add a page separator (optional)
                    if (pageNumber < pdf.numPages) {
                        var separator = document.createElement('div');
                        separator.className = 'page-separator';
                        pdfContainer.appendChild(separator);
                    }
                });
            }
        });

        // Disable right-click and save options
        document.addEventListener("contextmenu", function(e) {
            e.preventDefault();
        }, false);

        document.addEventListener("keydown", function(e) {
            if (e.ctrlKey && (e.key === "s" || e.key === "p")) {
                e.preventDefault();
            }
        });
    </script>
					 
					 
					 
                            </div>
                        </div>
                    </div>
                    <!--/Grid Form-->
                </div>
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