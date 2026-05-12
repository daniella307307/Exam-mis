<?php
include('header.php');

$ID = isset($_GET['ID']) ? (int)$_GET['ID'] : (int)($_POST['promotion_id'] ?? 0);
if ($ID <= 0) { echo "<p class='p-6 text-red-700'>Missing promotion ID.</p>"; exit; }

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $school_id        = (int)($_POST['promotion_school'] ?? 0);
    $certification_id = (int)($_POST['promotion_certification'] ?? 0);
    $year             = trim($_POST['promotion_year'] ?? '');
    $status           = ($_POST['promotion_status'] ?? '') === 'Inactive' ? 'Inactive' : 'Active';
    $name             = trim($_POST['promotion_name'] ?? '');
    $pay_usd          = (float)($_POST['promotion_pay_usd'] ?? 0);
    $pay_local        = (float)($_POST['promotion_pay_local'] ?? 0);
    $from             = $_POST['promotion_from'] ?: null;
    $to               = $_POST['promotion_to']   ?: null;

    if (!$school_id || !$certification_id || !ctype_digit($year) || strlen($year) !== 4) {
        $flash = ['type' => 'error', 'msg' => 'School, Certification and a 4-digit Year are required.'];
    } else {
        $school_row = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT school_region, country_ref FROM schools WHERE school_id = " . $school_id
        ));
        $region  = (int)($school_row['school_region'] ?? 0);
        $country = (int)($school_row['country_ref'] ?? 0);

        $upd = mysqli_prepare(
            $conn,
            "UPDATE students_promotion SET
                promotion_name = ?,
                promotion_certification = ?,
                promotion_pay_usd = ?,
                promotion_pay_local = ?,
                promotion_from = ?,
                promotion_to = ?,
                promotion_year = ?,
                promotion_region = ?,
                promotion_country = ?,
                promotion_school = ?,
                promotion_status = ?
             WHERE promotion_id = ?"
        );
        mysqli_stmt_bind_param(
            $upd, "siddsssiiisi",
            $name, $certification_id, $pay_usd, $pay_local,
            $from, $to, $year, $region, $country, $school_id, $status,
            $ID
        );
        if (mysqli_stmt_execute($upd)) {
            $flash = ['type' => 'ok', 'msg' => 'Promotion updated.'];
        } else {
            $flash = ['type' => 'error', 'msg' => 'Update failed: ' . mysqli_error($conn)];
        }
    }
}

$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students_promotion WHERE promotion_id = " . $ID));
if (!$row) { echo "<p class='p-6 text-red-700'>Promotion not found.</p>"; exit; }

$schools_q = mysqli_query($conn, "SELECT school_id, school_name FROM schools ORDER BY school_name");
$certs_q   = mysqli_query($conn, "SELECT certification_id, certification_name FROM certifications ORDER BY certification_name");
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-white-500 flex-1 p-4 overflow-hidden">
        <div class="max-w-3xl mx-auto bg-white border rounded shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Edit Promotion #<?php echo (int)$ID; ?></h2>
                <a href="Promotions" class="text-sm text-blue-600 hover:underline">← Back to Promotions</a>
            </div>

            <?php if ($flash): ?>
                <div class="<?php echo $flash['type'] === 'ok' ? 'bg-green-500 text-white' : 'bg-red-300 text-red-900'; ?> mb-4 px-4 py-2 rounded">
                    <?php echo htmlspecialchars($flash['msg']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="promotion_id" value="<?php echo (int)$ID; ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600">School</label>
                        <select name="promotion_school" required class="w-full px-3 py-2 bg-gray-100 rounded border">
                            <?php while ($s = mysqli_fetch_assoc($schools_q)): ?>
                                <option value="<?php echo (int)$s['school_id']; ?>"
                                    <?php echo ((int)$s['school_id'] === (int)$row['promotion_school']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['school_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Certification</label>
                        <select name="promotion_certification" required class="w-full px-3 py-2 bg-gray-100 rounded border">
                            <?php while ($c = mysqli_fetch_assoc($certs_q)): ?>
                                <option value="<?php echo (int)$c['certification_id']; ?>"
                                    <?php echo ((int)$c['certification_id'] === (int)$row['promotion_certification']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['certification_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Year</label>
                        <input type="number" name="promotion_year" value="<?php echo htmlspecialchars($row['promotion_year']); ?>" min="2000" max="2099" required
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Status</label>
                        <select name="promotion_status" class="w-full px-3 py-2 bg-gray-100 rounded border">
                            <option value="Active"   <?php echo $row['promotion_status'] === 'Active'   ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo $row['promotion_status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600">Promotion Name</label>
                        <input type="text" name="promotion_name" value="<?php echo htmlspecialchars((string)$row['promotion_name']); ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Pay (USD)</label>
                        <input type="number" step="0.01" name="promotion_pay_usd" value="<?php echo htmlspecialchars((string)$row['promotion_pay_usd']); ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Pay (Local)</label>
                        <input type="number" step="0.01" name="promotion_pay_local" value="<?php echo htmlspecialchars((string)$row['promotion_pay_local']); ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">From</label>
                        <input type="date" name="promotion_from" value="<?php echo htmlspecialchars((string)$row['promotion_from']); ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">To</label>
                        <input type="date" name="promotion_to" value="<?php echo htmlspecialchars((string)$row['promotion_to']); ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" name="save" class="px-6 py-2 bg-green-600 hover:bg-green-800 text-white rounded">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                    <a href="Delete_Promotion?ID=<?php echo (int)$ID; ?>"
                       class="ml-2 px-6 py-2 bg-red-500 hover:bg-red-700 text-white rounded inline-block">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </a>
                </div>
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
