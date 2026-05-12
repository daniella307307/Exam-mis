<?php
include('header.php');

$flash    = null;
$preview  = [];
$inserted = 0;
$skipped  = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_year     = trim($_POST['from_year'] ?? '');
    $to_year       = trim($_POST['to_year']   ?? '');
    $school_filter = (int)($_POST['school_id'] ?? 0);
    $only_active   = isset($_POST['only_active']) ? true : false;
    $mode          = $_POST['mode'] ?? 'preview';

    if (!ctype_digit($from_year) || !ctype_digit($to_year) || strlen($from_year) !== 4 || strlen($to_year) !== 4) {
        $flash = ['type' => 'error', 'msg' => 'Both years must be 4-digit numbers.'];
    } elseif ($from_year === $to_year) {
        $flash = ['type' => 'error', 'msg' => 'From-year and To-year must differ.'];
    } else {
        $where  = "promotion_year = ?";
        $types  = "s";
        $params = [$from_year];

        if ($only_active) {
            $where   .= " AND promotion_status = 'Active'";
        }
        if ($school_filter > 0) {
            $where   .= " AND promotion_school = ?";
            $types   .= "i";
            $params[] = $school_filter;
        }

        $src_sql  = "SELECT p.*, s.school_name, c.certification_name
                     FROM students_promotion p
                     LEFT JOIN schools s        ON p.promotion_school        = s.school_id
                     LEFT JOIN certifications c ON p.promotion_certification = c.certification_id
                     WHERE $where
                     ORDER BY s.school_name, c.certification_name";
        $src_stmt = mysqli_prepare($conn, $src_sql);
        mysqli_stmt_bind_param($src_stmt, $types, ...$params);
        mysqli_stmt_execute($src_stmt);
        $src_result = mysqli_stmt_get_result($src_stmt);

        $dup_stmt = mysqli_prepare($conn,
            "SELECT promotion_id FROM students_promotion
             WHERE promotion_school = ? AND promotion_certification = ? AND promotion_year = ?");

        $ins_stmt = mysqli_prepare($conn,
            "INSERT INTO students_promotion
             (promotion_name, promotion_certification, promotion_pay_usd, promotion_pay_local,
              promotion_from, promotion_to, promotion_year, promotion_region, promotion_country,
              promotion_school, promotion_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        while ($r = mysqli_fetch_assoc($src_result)) {
            $school_id = (int)$r['promotion_school'];
            $cert_id   = (int)$r['promotion_certification'];

            mysqli_stmt_bind_param($dup_stmt, "iis", $school_id, $cert_id, $to_year);
            mysqli_stmt_execute($dup_stmt);
            $dup_exists = mysqli_fetch_assoc(mysqli_stmt_get_result($dup_stmt));

            $row_status = $dup_exists ? 'skipped (already exists)' : 'will create';

            if (!$dup_exists && $mode === 'apply') {
                $name      = $to_year;
                $pay_usd   = (float)$r['promotion_pay_usd'];
                $pay_local = (float)$r['promotion_pay_local'];
                $from_d    = $r['promotion_from'];
                $to_d      = $r['promotion_to'];
                $region    = (int)$r['promotion_region'];
                $country   = (int)$r['promotion_country'];
                $status_v  = 'Active';

                mysqli_stmt_bind_param(
                    $ins_stmt, "siddsssiiis",
                    $name, $cert_id, $pay_usd, $pay_local,
                    $from_d, $to_d, $to_year, $region, $country, $school_id, $status_v
                );
                if (mysqli_stmt_execute($ins_stmt)) {
                    $row_status = 'created';
                    $inserted++;
                } else {
                    $row_status = 'error: ' . mysqli_error($conn);
                }
            } elseif ($dup_exists) {
                $skipped++;
            }

            $preview[] = [
                'school'        => $r['school_name'],
                'certification' => $r['certification_name'],
                'from_year'     => $r['promotion_year'],
                'to_year'       => $to_year,
                'result'        => $row_status,
            ];
        }

        if ($mode === 'apply') {
            $flash = ['type' => 'ok', 'msg' => "Rollover complete. Created: $inserted. Skipped: $skipped."];
        } else {
            $flash = ['type' => 'info', 'msg' => 'Preview only — nothing has been written. Submit again with "Apply" to execute.'];
        }
    }
}

$schools_q = mysqli_query($conn, "SELECT school_id, school_name FROM schools WHERE school_status='Active' ORDER BY school_name");
$years_q   = mysqli_query($conn, "SELECT DISTINCT promotion_year FROM students_promotion ORDER BY promotion_year DESC");
$years_list = [];
while ($y = mysqli_fetch_assoc($years_q)) { $years_list[] = $y['promotion_year']; }
$default_to = (string)((int)date('Y'));
$default_from = (string)((int)date('Y') - 1);
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-white-500 flex-1 p-4 overflow-hidden">
        <div class="max-w-5xl mx-auto bg-white border rounded shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Bulk Rollover Promotions</h2>
                <a href="Promotions" class="text-sm text-blue-600 hover:underline">← Back to Promotions</a>
            </div>

            <p class="text-sm text-gray-700 mb-4">
                Copies every promotion row from the <strong>From year</strong> into the <strong>To year</strong>.
                Existing rows (same school + certification + target year) are skipped, never overwritten.
                Use <strong>Preview</strong> first to see exactly what would be created.
            </p>

            <?php if ($flash): ?>
                <div class="<?php
                    echo $flash['type'] === 'ok'    ? 'bg-green-500 text-white'
                       : ($flash['type'] === 'info' ? 'bg-blue-100 text-blue-900'
                                                    : 'bg-red-300 text-red-900');
                ?> mb-4 px-4 py-2 rounded">
                    <?php echo htmlspecialchars($flash['msg']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm text-gray-600">From year</label>
                    <select name="from_year" class="w-full px-3 py-2 bg-gray-100 rounded border" required>
                        <?php foreach ($years_list as $y): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($y === ($_POST['from_year'] ?? $default_from)) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">To year</label>
                    <input type="number" name="to_year" min="2000" max="2099" required
                           value="<?php echo htmlspecialchars($_POST['to_year'] ?? $default_to); ?>"
                           class="w-full px-3 py-2 bg-gray-100 rounded border">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">School (optional)</label>
                    <select name="school_id" class="w-full px-3 py-2 bg-gray-100 rounded border">
                        <option value="0">All schools</option>
                        <?php while ($s = mysqli_fetch_assoc($schools_q)): ?>
                            <option value="<?php echo (int)$s['school_id']; ?>"
                                <?php echo ((int)($_POST['school_id'] ?? 0) === (int)$s['school_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['school_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Source filter</label>
                    <label class="block text-sm mt-2">
                        <input type="checkbox" name="only_active" value="1"
                            <?php echo isset($_POST['only_active']) ? 'checked' : 'checked'; ?>>
                        Only roll over Active rows
                    </label>
                </div>

                <div class="md:col-span-4 flex gap-3">
                    <button type="submit" name="mode" value="preview"
                            class="px-5 py-2 bg-gray-700 hover:bg-gray-900 text-white rounded">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                    <button type="submit" name="mode" value="apply"
                            onclick="return confirm('Apply the rollover? This will INSERT promotion rows.');"
                            class="px-5 py-2 bg-purple-700 hover:bg-purple-900 text-white rounded">
                        <i class="fas fa-check mr-1"></i> Apply Rollover
                    </button>
                </div>
            </form>

            <?php if (!empty($preview)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-300 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2">School</th>
                                <th class="border px-3 py-2">Certification</th>
                                <th class="border px-3 py-2">From year</th>
                                <th class="border px-3 py-2">To year</th>
                                <th class="border px-3 py-2">Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($preview as $p): ?>
                                <tr>
                                    <td class="border px-3 py-1"><?php echo htmlspecialchars((string)$p['school']); ?></td>
                                    <td class="border px-3 py-1"><?php echo htmlspecialchars((string)$p['certification']); ?></td>
                                    <td class="border px-3 py-1 text-center"><?php echo htmlspecialchars((string)$p['from_year']); ?></td>
                                    <td class="border px-3 py-1 text-center"><?php echo htmlspecialchars((string)$p['to_year']); ?></td>
                                    <td class="border px-3 py-1">
                                        <?php
                                            $r = (string)$p['result'];
                                            $cls = strpos($r, 'created') !== false ? 'text-green-700'
                                                 : (strpos($r, 'skipped') !== false ? 'text-yellow-700'
                                                 : (strpos($r, 'will create') !== false ? 'text-blue-700' : 'text-red-700'));
                                        ?>
                                        <span class="<?php echo $cls; ?>"><?php echo htmlspecialchars($r); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
