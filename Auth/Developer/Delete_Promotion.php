<?php
include('header.php');

$ID = isset($_GET['ID']) ? (int)$_GET['ID'] : (int)($_POST['promotion_id'] ?? 0);
if ($ID <= 0) { echo "<p class='p-6 text-red-700'>Missing promotion ID.</p>"; exit; }

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $invoice_check = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) AS cnt FROM students_invoice WHERE invoice_promotion = " . $ID
    ));
    $linked_invoices = (int)($invoice_check['cnt'] ?? 0);

    if ($linked_invoices > 0 && !isset($_POST['force'])) {
        $flash = ['type' => 'error', 'msg' => "This promotion has $linked_invoices linked student invoice(s). Tick 'Force delete' to proceed anyway."];
    } else {
        $del = mysqli_prepare($conn, "DELETE FROM students_promotion WHERE promotion_id = ?");
        mysqli_stmt_bind_param($del, "i", $ID);
        if (mysqli_stmt_execute($del)) {
            ?>
            <div class="p-6 max-w-xl mx-auto bg-green-100 text-green-800 rounded mt-6">
                Promotion #<?php echo (int)$ID; ?> deleted.
                <a href="Promotions" class="block mt-3 text-blue-700 underline">Return to Promotions</a>
            </div>
            </body></html>
            <?php
            exit;
        } else {
            $flash = ['type' => 'error', 'msg' => 'Delete failed: ' . mysqli_error($conn)];
        }
    }
}

$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT p.*, s.school_name, c.certification_name
     FROM students_promotion p
     LEFT JOIN schools s        ON p.promotion_school        = s.school_id
     LEFT JOIN certifications c ON p.promotion_certification = c.certification_id
     WHERE p.promotion_id = " . $ID
));
if (!$row) { echo "<p class='p-6 text-red-700'>Promotion not found.</p>"; exit; }

$linked = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS cnt FROM students_invoice WHERE invoice_promotion = " . $ID
));
$linked_invoices = (int)($linked['cnt'] ?? 0);
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-white-500 flex-1 p-4 overflow-hidden">
        <div class="max-w-xl mx-auto bg-white border rounded shadow-sm p-6">
            <h2 class="text-xl font-bold text-red-700 mb-3">Delete Promotion?</h2>

            <?php if ($flash): ?>
                <div class="bg-red-200 text-red-900 px-4 py-2 mb-4 rounded">
                    <?php echo htmlspecialchars($flash['msg']); ?>
                </div>
            <?php endif; ?>

            <p class="text-sm text-gray-700 mb-3">
                You are about to permanently delete the following promotion:
            </p>
            <ul class="bg-gray-50 border rounded p-4 text-sm space-y-1 mb-4">
                <li><strong>ID:</strong> <?php echo (int)$row['promotion_id']; ?></li>
                <li><strong>Name:</strong> <?php echo htmlspecialchars((string)$row['promotion_name']); ?></li>
                <li><strong>School:</strong> <?php echo htmlspecialchars((string)$row['school_name']); ?></li>
                <li><strong>Certification:</strong> <?php echo htmlspecialchars((string)$row['certification_name']); ?></li>
                <li><strong>Year:</strong> <?php echo htmlspecialchars((string)$row['promotion_year']); ?></li>
                <li><strong>Status:</strong> <?php echo htmlspecialchars((string)$row['promotion_status']); ?></li>
                <li><strong>Linked student invoices:</strong>
                    <span class="<?php echo $linked_invoices > 0 ? 'text-red-700 font-bold' : ''; ?>">
                        <?php echo $linked_invoices; ?>
                    </span>
                </li>
            </ul>

            <form method="POST">
                <input type="hidden" name="promotion_id" value="<?php echo (int)$ID; ?>">
                <?php if ($linked_invoices > 0): ?>
                    <label class="block mb-3 text-sm text-red-700">
                        <input type="checkbox" name="force" value="1"> Force delete (will leave orphaned invoices).
                    </label>
                <?php endif; ?>
                <button type="submit" name="confirm_delete" class="bg-red-600 hover:bg-red-800 text-white px-4 py-2 rounded">
                    <i class="fas fa-trash mr-1"></i> Yes, delete
                </button>
                <a href="Promotions" class="ml-2 px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded inline-block">Cancel</a>
            </form>
        </div>
    </main>
</div>

<footer class="bg-grey-darkest text-white p-2">
    <div class="flex flex-1 mx-auto">&copy; My Design</div>
</footer>
</div>
</div>
<script src="../../main.js"></script>
</body>
</html>
