<?php
include('header.php');

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $school_id      = (int)($_POST['promotion_school'] ?? 0);
    $cert_ids_raw   = $_POST['promotion_certifications'] ?? [];
    $cert_ids       = array_values(array_unique(array_filter(array_map('intval', (array)$cert_ids_raw))));
    $year           = trim($_POST['promotion_year'] ?? '');
    $status         = ($_POST['promotion_status'] ?? '') === 'Inactive' ? 'Inactive' : 'Active';
    $name           = trim($_POST['promotion_name'] ?? '');
    $pay_usd        = (float)($_POST['promotion_pay_usd'] ?? 0);
    $pay_local      = (float)($_POST['promotion_pay_local'] ?? 0);
    $from           = $_POST['promotion_from'] ?? null;
    $to             = $_POST['promotion_to']   ?? null;

    if (!$school_id || empty($cert_ids) || !ctype_digit($year) || strlen($year) !== 4) {
        $flash = ['type' => 'error', 'msg' => 'School, at least one Certification, and a 4-digit Year are required.'];
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

        $created = 0; $skipped = 0; $errors = 0; $error_msgs = [];

        foreach ($cert_ids as $cert_id) {
            mysqli_stmt_bind_param($dup, "iis", $school_id, $cert_id, $year);
            mysqli_stmt_execute($dup);
            $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($dup));
            if ($exists) { $skipped++; continue; }

            mysqli_stmt_bind_param(
                $ins, "siddsssiiis",
                $name, $cert_id, $pay_usd, $pay_local,
                $from_v, $to_v, $year, $region, $country, $school_id, $status
            );
            if (mysqli_stmt_execute($ins)) {
                $created++;
            } else {
                $errors++;
                $error_msgs[] = 'cert ' . $cert_id . ': ' . mysqli_error($conn);
            }
        }

        $msg = "Created $created · Skipped $skipped (already existed)";
        if ($errors > 0) {
            $msg .= " · Errors $errors — " . implode('; ', array_slice($error_msgs, 0, 3));
            $flash = ['type' => 'error', 'msg' => $msg];
        } else {
            $flash = ['type' => 'ok', 'msg' => $msg];
        }
    }
}

$schools_q = mysqli_query($conn, "SELECT school_id, school_name FROM schools WHERE school_status='Active' ORDER BY school_name");

// Load certifications, split into Grade group and Other group, natural-sort the grades.
$all_certs = [];
$cres = mysqli_query($conn, "SELECT certification_id, certification_name FROM certifications ORDER BY certification_name");
while ($c = mysqli_fetch_assoc($cres)) { $all_certs[] = $c; }

$grade_certs = [];
$other_certs = [];
foreach ($all_certs as $c) {
    if (preg_match('/^\s*GRADE\s+\d+/i', $c['certification_name']) || preg_match('/^\s*Nursary\s+/i', $c['certification_name'])) {
        $grade_certs[] = $c;
    } else {
        $other_certs[] = $c;
    }
}
usort($grade_certs, function ($a, $b) {
    $rank = function ($n) {
        if (preg_match('/Nursary\s+([IVX]+)/i', $n, $m)) {
            $roman = ['I'=>1,'II'=>2,'III'=>3,'IV'=>4,'V'=>5,'VI'=>6];
            return ($roman[strtoupper($m[1])] ?? 0) - 100; // nursary sorts before grades
        }
        if (preg_match('/GRADE\s+(\d+)/i', $n, $m)) return (int)$m[1];
        return 9999;
    };
    return $rank($a['certification_name']) <=> $rank($b['certification_name']);
});

$default_year = date('Y');

// Pre-fill the form on a failed POST.
$posted_school = (int)($_POST['promotion_school'] ?? 0);
$posted_certs  = array_map('intval', (array)($_POST['promotion_certifications'] ?? []));
$posted_year   = trim($_POST['promotion_year'] ?? '');
$posted_status = $_POST['promotion_status'] ?? 'Active';
$posted_name   = trim($_POST['promotion_name'] ?? '');
$posted_usd    = $_POST['promotion_pay_usd'] ?? '0';
$posted_local  = $_POST['promotion_pay_local'] ?? '0';
$posted_from   = $_POST['promotion_from'] ?? '';
$posted_to     = $_POST['promotion_to'] ?? '';
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-white-500 flex-1 p-4 overflow-hidden">
        <div class="max-w-4xl mx-auto bg-white border rounded shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Add Promotion(s)</h2>
                <a href="Promotions" class="text-sm text-blue-600 hover:underline">← Back to Promotions</a>
            </div>

            <p class="text-sm text-gray-600 mb-4">
                Pick a school and tick every certification (or grade) you want to roll out. One promotion row is
                created per ticked certification. Rows that already exist for the same school + cert + year are
                skipped automatically.
            </p>

            <?php if ($flash): ?>
                <div class="<?= $flash['type'] === 'ok' ? 'bg-green-500 text-white' : 'bg-red-300 text-red-900' ?> mb-4 px-4 py-2 rounded">
                    <?= htmlspecialchars($flash['msg']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600">School</label>
                        <select name="promotion_school" required class="w-full px-3 py-2 bg-gray-100 rounded border">
                            <option value="">— Select school —</option>
                            <?php while ($s = mysqli_fetch_assoc($schools_q)): ?>
                                <option value="<?= (int)$s['school_id'] ?>" <?= $posted_school === (int)$s['school_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['school_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Year</label>
                        <input type="number" name="promotion_year" min="2000" max="2099" required
                               value="<?= htmlspecialchars($posted_year !== '' ? $posted_year : $default_year) ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                </div>

                <!-- Multi-cert picker -->
                <div class="border rounded p-3 bg-gray-50">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <label class="text-sm text-gray-700 font-semibold">Certifications (tick all that apply)</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" id="cert_filter" placeholder="Filter…"
                                   class="px-2 py-1 text-sm bg-white border rounded">
                            <button type="button" id="cert_all"
                                    class="text-xs px-2 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded">Select all visible</button>
                            <button type="button" id="cert_none"
                                    class="text-xs px-2 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded">Clear</button>
                            <button type="button" id="cert_grades"
                                    class="text-xs px-2 py-1 bg-purple-100 hover:bg-purple-200 text-purple-800 rounded">All grades</button>
                        </div>
                    </div>

                    <?php if (!empty($grade_certs)): ?>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Grade certifications</p>
                        <div class="cert-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 mb-3">
                            <?php foreach ($grade_certs as $c):
                                $cid = (int)$c['certification_id'];
                                $checked = in_array($cid, $posted_certs, true) ? 'checked' : '';
                            ?>
                                <label class="cert-row flex items-center gap-2 px-2 py-1 bg-white border rounded hover:bg-purple-50">
                                    <input type="checkbox" name="promotion_certifications[]" value="<?= $cid ?>" <?= $checked ?>
                                           class="cert-cb grade-cb">
                                    <span class="text-sm"><?= htmlspecialchars($c['certification_name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($other_certs)): ?>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Other certifications</p>
                        <div class="cert-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1">
                            <?php foreach ($other_certs as $c):
                                $cid = (int)$c['certification_id'];
                                $checked = in_array($cid, $posted_certs, true) ? 'checked' : '';
                            ?>
                                <label class="cert-row flex items-center gap-2 px-2 py-1 bg-white border rounded hover:bg-purple-50">
                                    <input type="checkbox" name="promotion_certifications[]" value="<?= $cid ?>" <?= $checked ?>
                                           class="cert-cb">
                                    <span class="text-sm"><?= htmlspecialchars($c['certification_name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <p class="text-xs text-gray-500 mt-2"><span id="cert_count">0</span> selected</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600">Label (optional — applied to every created promotion)</label>
                        <input type="text" name="promotion_name" value="<?= htmlspecialchars($posted_name) ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border" placeholder="e.g. 2026 Cohort">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Status</label>
                        <select name="promotion_status" class="w-full px-3 py-2 bg-gray-100 rounded border">
                            <option value="Active"   <?= $posted_status === 'Active'   ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= $posted_status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div></div>
                    <div>
                        <label class="block text-sm text-gray-600">Pay (USD)</label>
                        <input type="number" step="0.01" name="promotion_pay_usd" value="<?= htmlspecialchars((string)$posted_usd) ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Pay (Local)</label>
                        <input type="number" step="0.01" name="promotion_pay_local" value="<?= htmlspecialchars((string)$posted_local) ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">From</label>
                        <input type="date" name="promotion_from" value="<?= htmlspecialchars($posted_from) ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">To</label>
                        <input type="date" name="promotion_to" value="<?= htmlspecialchars($posted_to) ?>"
                               class="w-full px-3 py-2 bg-gray-100 rounded border">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" name="create" class="px-6 py-2 bg-blue-700 hover:bg-blue-900 text-white rounded">
                        <i class="fas fa-plus mr-1"></i> Create Promotion(s)
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
(function () {
    var filterInput = document.getElementById('cert_filter');
    var allBtn      = document.getElementById('cert_all');
    var noneBtn     = document.getElementById('cert_none');
    var gradesBtn   = document.getElementById('cert_grades');
    var countEl     = document.getElementById('cert_count');
    var rows        = Array.prototype.slice.call(document.querySelectorAll('.cert-row'));

    function visibleRows() { return rows.filter(function (r) { return r.style.display !== 'none'; }); }
    function updateCount() {
        var n = document.querySelectorAll('.cert-cb:checked').length;
        if (countEl) countEl.textContent = String(n);
    }

    if (filterInput) {
        filterInput.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            rows.forEach(function (r) {
                var t = (r.textContent || '').toLowerCase();
                r.style.display = q === '' || t.indexOf(q) !== -1 ? '' : 'none';
            });
        });
    }
    if (allBtn) {
        allBtn.addEventListener('click', function () {
            visibleRows().forEach(function (r) {
                var cb = r.querySelector('.cert-cb');
                if (cb) cb.checked = true;
            });
            updateCount();
        });
    }
    if (noneBtn) {
        noneBtn.addEventListener('click', function () {
            document.querySelectorAll('.cert-cb').forEach(function (cb) { cb.checked = false; });
            updateCount();
        });
    }
    if (gradesBtn) {
        gradesBtn.addEventListener('click', function () {
            document.querySelectorAll('.grade-cb').forEach(function (cb) { cb.checked = true; });
            updateCount();
        });
    }
    document.querySelectorAll('.cert-cb').forEach(function (cb) {
        cb.addEventListener('change', updateCount);
    });
    updateCount();
})();
</script>

<footer class="bg-grey-darkest text-white p-2">
    <div class="flex flex-1 mx-auto">&copy; My Design</div>
</footer>
</div>
</div>
<script src="../../main.js"></script>
</body>
</html>
