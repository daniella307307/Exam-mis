<?php
include('header.php');

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $school_id        = (int)($_POST['promotion_school'] ?? 0);
    $certification_id = (int)($_POST['promotion_certification'] ?? 0);
    $year             = trim($_POST['promotion_year'] ?? '');
    $status           = ($_POST['promotion_status'] ?? '') === 'Inactive' ? 'Inactive' : 'Active';
    $name             = trim($_POST['promotion_name'] ?? '');
    $pay_usd          = (float)($_POST['promotion_pay_usd'] ?? 0);
    $pay_local        = (float)($_POST['promotion_pay_local'] ?? 0);
    $from             = $_POST['promotion_from'] ?? null;
    $to               = $_POST['promotion_to']   ?? null;

    if (!$school_id || !$certification_id || !ctype_digit($year) || strlen($year) !== 4) {
        $flash = ['type' => 'error', 'msg' => 'School, Certification and a 4-digit Year are required.'];
    } else {
        $school_row = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT school_region, country_ref FROM schools WHERE school_id = " . $school_id
        ));
        $region  = (int)($school_row['school_region'] ?? 0);
        $country = (int)($school_row['country_ref'] ?? 0);

        $dup = mysqli_prepare(
            $conn,
            "SELECT promotion_id FROM students_promotion
             WHERE promotion_school = ? AND promotion_certification = ? AND promotion_year = ?"
        );
        mysqli_stmt_bind_param($dup, "iis", $school_id, $certification_id, $year);
        mysqli_stmt_execute($dup);
        $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($dup));

        if ($exists) {
            $flash = ['type' => 'error', 'msg' => 'A promotion already exists for that school + certification + year.'];
        } else {
            $ins = mysqli_prepare(
                $conn,
                "INSERT INTO students_promotion
                 (promotion_name, promotion_certification, promotion_pay_usd, promotion_pay_local,
                  promotion_from, promotion_to, promotion_year, promotion_region, promotion_country,
                  promotion_school, promotion_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $from_v = $from ?: null;
            $to_v   = $to   ?: null;
            mysqli_stmt_bind_param(
                $ins, "siddsssiiis",
                $name, $certification_id, $pay_usd, $pay_local,
                $from_v, $to_v, $year, $region, $country, $school_id, $status
            );
            if (mysqli_stmt_execute($ins)) {
                $flash = ['type' => 'ok', 'msg' => 'Promotion created.'];
            } else {
                $flash = ['type' => 'error', 'msg' => 'Insert failed: ' . mysqli_error($conn)];
            }
        }
    }
}

$schools_q = mysqli_query($conn, "SELECT school_id, school_name FROM schools WHERE school_status='Active' ORDER BY school_name");
$certs_q   = mysqli_query($conn, "SELECT certification_id, certification_name FROM certifications ORDER BY certification_name");
$default_year = date('Y');
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-white-500 flex-1 p-4 overflow-hidden">
        <div class="max-w-3xl mx-auto bg-white border rounded shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Add Promotion</h2>
                <a href="Promotions" class="text-sm text-blue-600 hover:underline">← Back to Promotions</a>
            </div>

            <?php if ($flash): ?>
                <div class="<?php echo $flash['type'] === 'ok' ? 'bg-green-500 text-white' : 'bg-red-300 text-red-900'; ?> mb-4 px-4 py-2 rounded">
                    <?php echo htmlspecialchars($flash['msg']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600">School</label>
                        <select name="promotion_school" required class="w-full px-3 py-2 bg-gray-100 rounded border">
                            <option value="">— Select school —</option>
                            <?php while ($s = mysqli_fetch_assoc($schools_q)): ?>
                                <option value="<?php echo (int)$s['school_id']; ?>">
                                    <?php echo htmlspecialchars($s['school_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Certification</label>
                        <select name="promotion_certification" required class="w-full px-3 py-2 bg-gray-100 rounded border">
                            <option value="">— Select certification —</option>
                            <?php while ($c = mysqli_fetch_assoc($certs_q)): ?>
                                <option value="<?php echo (int)$c['certification_id']; ?>">
                                    <?php echo htmlspecialchars($c['certification_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Year</label>
                        <input type="number" name="promotion_year" value="<?php echo $default_year; ?>" min="2000" max="2099" required
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Status</label>
                        <select name="promotion_status" class="w-full px-3 py-2 bg-gray-100 rounded border">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600">Promotion Name (label, optional)</label>
                        <input type="text" name="promotion_name" class="w-full px-3 py-2 bg-gray-100 rounded border" placeholder="e.g. 2026 Cohort">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Pay (USD)</label>
                        <input type="number" step="0.01" name="promotion_pay_usd" value="0" class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Pay (Local)</label>
                        <input type="number" step="0.01" name="promotion_pay_local" value="0" class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">From</label>
                        <input type="date" name="promotion_from" class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">To</label>
                        <input type="date" name="promotion_to" class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" name="create" class="px-6 py-2 bg-blue-700 hover:bg-blue-900 text-white rounded">
                        <i class="fas fa-plus mr-1"></i> Create Promotion
                    </button>
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
