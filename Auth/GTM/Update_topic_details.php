<?php
ob_start();
include('header.php');

// Protect GET variables
$COURSE = $_GET['COURSE'] ?? '';
$CERTIFICATE = $_GET['CERTIFICATE'] ?? '';
$STATUS = $_GET['STATUS'] ?? '';
$CURRENT = $_GET['CURRENT'] ?? '';
$ID = $_GET['ID'] ?? '';

mysqli_set_charset($conn, "utf8mb4");

// Fetch topic details
$details_topic = mysqli_fetch_array(mysqli_query($conn, "
    SELECT * FROM learning_topics
    LEFT JOIN learning_weeks ON learning_topics.topic_week = learning_weeks.week_id
    LEFT JOIN certifications ON learning_topics.topic_certification = certifications.certification_id 
    WHERE topic_id = '$ID'
"));
?>
<!--/Header-->

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php');?>

<body class="h-screen font-sans login bg-cover">
<div class="container mx-auto h-full flex flex-1 justify-center items-center">
    <div class="w-full max-w-lg">
        <div class="leading-loose">

            <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                <p class="text-gray-800 font-medium">Add New Topic</p>

                <div>
                    <label class="block text-sm text-gray-600">Topic Details</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                        name="topic_title"
                        value="<?php echo htmlspecialchars($details_topic['topic_title']); ?>"
                        type="text" placeholder="Topic Details" required>
                </div>

                <div>
                    <label class="block text-sm text-gray-600">Topic Details in French</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                        name="topic_french"
                        value="<?php echo htmlspecialchars($details_topic['topic_french']); ?>"
                        type="text" placeholder="Topic Details in French" required>
                </div>

                <div>
                    <label class="block text-sm text-gray-600">Topic Document</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                        name="topic_document"
                        value="<?php echo htmlspecialchars($details_topic['topic_document']); ?>"
                        type="url" placeholder="URL">
                </div>

                <div>
                    <label class="block text-sm text-gray-600">Topic Document (French)</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                        name="topic_document_french"
                        value="<?php echo htmlspecialchars($details_topic['topic_document_french']); ?>"
                        type="url" placeholder="URL (French)">
                </div>

                <div>
                    <label class="block text-sm text-gray-600">Topic Video</label>
                    <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                        name="topic_video"
                        value="<?php echo htmlspecialchars($details_topic['topic_video']); ?>"
                        type="url" placeholder="Video URL">
                </div>

                <div class="flex flex-wrap -mx-3 mb-2">

                    <div class="w-full md:w-1/2 px-3">
                        <label class="block uppercase tracking-wide text-xs mb-1">Week</label>
                        <select name="topic_week" class="block w-full bg-gray-200 border text-gray-700 py-3 px-4 rounded">
                            <option value="<?php echo $details_topic['topic_week']; ?>">
                                <?php echo $details_topic['week_description']; ?>
                            </option>
                            <?php
                            $select_weeks = mysqli_query($conn,"SELECT * FROM learning_weeks");
                            while($week = mysqli_fetch_array($select_weeks)){
                            ?>
                                <option value="<?php echo $week['week_id']; ?>">
                                    <?php echo $week['week_description']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="w-full md:w-1/2 px-3">
                        <label class="block uppercase tracking-wide text-xs mb-1">Status</label>
                        <select name="topic_status" class="block w-full bg-gray-200 border text-gray-700 py-3 px-4 rounded">
                            <option value="<?php echo $details_topic['topic_status']; ?>">
                                <?php echo $details_topic['topic_status']; ?>
                            </option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                </div>

<?php
if(isset($_POST['Update'])){

    $topic_week = mysqli_real_escape_string($conn, $_POST['topic_week']);
    $topic_title = mysqli_real_escape_string($conn, $_POST['topic_title']);
    $topic_french = mysqli_real_escape_string($conn, $_POST['topic_french']);
    $topic_document = mysqli_real_escape_string($conn, $_POST['topic_document']);
    $topic_document_french = mysqli_real_escape_string($conn, $_POST['topic_document_french']);
    $topic_video = mysqli_real_escape_string($conn, $_POST['topic_video']);
    $topic_status = mysqli_real_escape_string($conn, $_POST['topic_status']);

    $check = mysqli_num_rows(mysqli_query($conn,
        "SELECT * FROM learning_topics WHERE topic_title='$topic_title' AND topic_id !='$ID'"
    ));

    if($check > 0){
        echo "<div class='bg-red-300 p-3 rounded'>Topic already exists!</div>";
    } else {

        $update = mysqli_query($conn,"UPDATE learning_topics SET 
            topic_week = '$topic_week',
            topic_title = '$topic_title',
            topic_french = '$topic_french',
            topic_document = '$topic_document',
            topic_document_french = '$topic_document_french',
            topic_video = '$topic_video',
            topic_status = '$topic_status'
            WHERE topic_id = '$ID'"
        );

        if($update){
            header("Location: Module_topics?COURSE=$COURSE&CERTIFICATE=$CERTIFICATE&STATUS=$topic_status");
            exit;
        } else {
            echo "<div class='bg-red-300 p-3 rounded'>Error updating topic!</div>";
        }
    }
}
?>

                <div class="mt-4">
                    <center>
                        <button type="submit" name="Update"
                            class="px-4 py-1 text-white bg-green-500 rounded">
                            Update Topic Details
                        </button>
                    </center>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>
