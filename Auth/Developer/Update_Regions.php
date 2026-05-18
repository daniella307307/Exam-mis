<?php
session_start();
include('../../db.php');

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $STATUS  = $_GET['STATUS']  ?? 'Active';
    $CURRENT = $_GET['CURRENT'] ?? 'Active';
    $ID      = (int)($_GET['ID'] ?? 0);

    if (!in_array($STATUS,  ['Active', 'Inactive'], true)) { $STATUS  = 'Active'; }
    if (!in_array($CURRENT, ['Active', 'Inactive'], true)) { $CURRENT = 'Active'; }

    if ($ID > 0) {
        $upd = mysqli_prepare($conn, "UPDATE regions_table SET region_status = ? WHERE region_id = ?");
        mysqli_stmt_bind_param($upd, 'si', $STATUS, $ID);
        if (mysqli_stmt_execute($upd)) {
            mysqli_stmt_close($upd);
            ?>
            <script>window.setTimeout(function(){
                window.location.href = "Regions?STATUS=<?= htmlspecialchars($CURRENT) ?>";
            }, 10);</script>
            <?php
        }
    }
}
?>
