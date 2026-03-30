<?php
include('header.php');
include('Access.php');

/* ---------------- SAFE GET PARAMETERS ---------------- */
$CERTIFICATE = isset($_GET['CERTIFICATE'])
    ? mysqli_real_escape_string($conn, $_GET['CERTIFICATE'])
    : '';

$LANG = isset($_GET['LANG']) && !empty($_GET['LANG'])
    ? $_GET['LANG']
    : $school_language;

/* ---------------- FETCH CERTIFICATION INFO ---------------- */
$cert_query = mysqli_query(
    $conn,
    "SELECT certification_name, certification_duration 
     FROM certifications 
     WHERE certification_id='$CERTIFICATE'"
);

$cert = mysqli_fetch_assoc($cert_query);

$certification_name     = $cert['certification_name'] ?? 'Unknown Certification';
$certification_duration = $cert['certification_duration'] ?? '0';

/* ---------------- LANGUAGE SETTING ---------------- */
/* This should ideally come from DB */
$language_setting = $school_language;

/* ---------------- STATUS HANDLING ---------------- */
if (isset($_GET['STATUS']) && ($_GET['STATUS'] === 'Active' || $_GET['STATUS'] === 'Inactive')) {
    $STATUS = $_GET['STATUS'];
} else {
    $STATUS = 'Active';
}

$action = "course_certificate='$CERTIFICATE' AND course_status='$STATUS'";

/* ---------------- FETCH MODULES ---------------- */
$select_modules = mysqli_query(
    $conn,
    "SELECT * FROM certification_courses WHERE $action"
);
?>

<!-- ===================== PAGE CONTENT ===================== -->

<div class="flex flex-1">
    <?php include('side_bar_courses.php'); ?>

    <!-- Main -->
    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">

            <!-- ================= HEADER ================= -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border border-gray-300 rounded shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-b flex justify-between items-center">

                        <!-- LEFT: STATUS BUTTONS -->
                        <div class="flex items-center">
                            <span class="mr-4 font-bold">Certification</span>

                            <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE; ?>&LANG=<?php echo $LANG; ?>&STATUS=Active" class="mr-3">
                                <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Active
                                </button>
                            </a>

                            <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE; ?>&LANG=<?php echo $LANG; ?>&STATUS=Inactive">
                                <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Inactive
                                </button>
                            </a>
                        </div>

                        <!-- RIGHT: LANGUAGE SWITCH -->
                        <div class="flex items-center">
                            <?php if ($language_setting === "Bilingual") : ?>

                                <?php if ($LANG === "French" || $LANG === "FR") : ?>
                                    <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE; ?>&STATUS=<?php echo $STATUS; ?>&LANG=ENG">
                                        <button class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                            Switch to ENG
                                        </button>
                                    </a>
                                <?php else : ?>
                                    <a href="Modules_per_Certification?CERTIFICATE=<?php echo $CERTIFICATE; ?>&STATUS=<?php echo $STATUS; ?>&LANG=FR">
                                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            Switch to FR
                                        </button>
                                    </a>
                                <?php endif; ?>

                            <?php else : ?>
                                <button class="bg-gray-400 cursor-not-allowed text-white font-bold py-2 px-4 rounded">
                                    <?php echo ($language_setting === "French" || $language_setting === "FR") ? "FR" : "ENG"; ?>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- ================= CONTENT ================= -->
                    <div class="p-3">
                        <p><strong><big><?php echo $certification_name; ?> Courses</big></strong></p>
                        <p><strong><big>Duration: <?php echo $certification_duration; ?> months</big></strong></p>
                        <br>

                        <p><strong>Current Language:</strong> <?php echo $LANG; ?></p>
                        <p><strong>System Language Setting:</strong> <?php echo $language_setting; ?></p>

                        <br>

                        <!-- ================= TABLE ================= -->
                        <table class="table-responsive w-full rounded border">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2">#</th>
                                    <th class="border px-4 py-2">Module Code</th>
                                    <th class="border px-4 py-2">Module Name</th>
                                    <th class="border px-4 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($module = mysqli_fetch_assoc($select_modules)) : ?>
                                    <tr>
                                        <td class="border px-4 py-1">
                                            <?php echo $module['course_id']; ?>
                                        </td>

                                        <td class="border px-4 py-1">
                                            <a href="Module_topics?COURSE=<?php echo $module['course_id']; ?>&CERTIFICATE=<?php echo $CERTIFICATE; ?>&LANG=<?php echo $LANG; ?>">
                                                <?php echo $module['course_code']; ?>
                                                <i class="fas fa-book text-green-500 mx-2"></i>
                                            </a>
                                        </td>

                                        <td class="border px-4 py-2">
                                            <?php
                                            if ($LANG === "French" || $LANG === "FR") {
                                                echo !empty($module['course_french'])
                                                    ? $module['course_french']
                                                    : $module['course_name'];
                                            } else {
                                                echo $module['course_name'];
                                            }
                                            ?>
                                        </td>

                                        <td class="border px-4 py-2 text-center">
                                            <?php if ($STATUS === "Active") : ?>
                                                <i class="fas fa-unlock text-green-500"></i>
                                            <?php else : ?>
                                                <i class="fas fa-lock text-red-500"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ================= FOOTER ================= -->
<?php include('footer.php'); ?>

<script src="../../main.js"></script>
</body>
</html>
