<?php
/**
 * My Visited Modules — shows ONLY modules this user has previously opened.
 *
 * Reached from Current_Courses.php (the small "Modules" link next to each
 * promotion row). It is intentionally NOT a full module catalogue: that
 * lives at Modules_per_Certification.php, accessed via the Certification /
 * Promotion side. This page tells the user that explicitly so they don't
 * mistake an empty list for "no modules exist".
 *
 * Visit rows are written from Module_topics.php whenever the user opens a
 * module's topics page. Table is created idempotently below.
 */
include('header.php');

/* ---------------- Idempotent table ---------------- */
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS user_module_visits (
        visit_id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id           INT NOT NULL,
        course_id         INT NOT NULL,
        certification_id  INT NOT NULL,
        first_visited_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_visited_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        visit_count       INT NOT NULL DEFAULT 1,
        UNIQUE KEY uniq_user_course (user_id, course_id),
        KEY idx_user_cert (user_id, certification_id)
    )
");

/* ---------------- Params ---------------- */
$CERTIFICATE = isset($_GET['CERTIFICATE']) ? (int)$_GET['CERTIFICATE'] : 0;
$LANG        = isset($_GET['LANG']) && !empty($_GET['LANG']) ? $_GET['LANG'] : $school_language;
$user_id     = (int)$_SESSION['user_id'];

/* ---------------- Certification name for header ---------------- */
$certification_name = '';
if ($CERTIFICATE > 0) {
    $cstmt = $conn->prepare("SELECT certification_name FROM certifications WHERE certification_id = ? LIMIT 1");
    $cstmt->bind_param('i', $CERTIFICATE);
    $cstmt->execute();
    $crow = $cstmt->get_result()->fetch_assoc();
    $cstmt->close();
    $certification_name = $crow['certification_name'] ?? '';
}

/* ---------------- Fetch this user's visited modules ----------------
 * Filtered by CERTIFICATE when provided. Joined to certification_courses
 * so a module renamed or deactivated since the visit still renders sanely.
 */
if ($CERTIFICATE > 0) {
    $vsql = "SELECT v.course_id, v.certification_id, v.first_visited_at, v.last_visited_at, v.visit_count,
                    cc.course_code, cc.course_name, cc.course_french, cc.course_status,
                    c.certification_name
               FROM user_module_visits v
               LEFT JOIN certification_courses cc ON cc.course_id        = v.course_id
               LEFT JOIN certifications        c  ON c.certification_id  = v.certification_id
              WHERE v.user_id = ? AND v.certification_id = ?
              ORDER BY v.last_visited_at DESC";
    $vstmt = $conn->prepare($vsql);
    $vstmt->bind_param('ii', $user_id, $CERTIFICATE);
} else {
    $vsql = "SELECT v.course_id, v.certification_id, v.first_visited_at, v.last_visited_at, v.visit_count,
                    cc.course_code, cc.course_name, cc.course_french, cc.course_status,
                    c.certification_name
               FROM user_module_visits v
               LEFT JOIN certification_courses cc ON cc.course_id        = v.course_id
               LEFT JOIN certifications        c  ON c.certification_id  = v.certification_id
              WHERE v.user_id = ?
              ORDER BY v.last_visited_at DESC";
    $vstmt = $conn->prepare($vsql);
    $vstmt->bind_param('i', $user_id);
}
$vstmt->execute();
$visits = $vstmt->get_result();
$visit_count = $visits->num_rows;
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">

        <!-- Back button + breadcrumb. Same style as the curriculum_* pages. -->
        <div class="flex items-center gap-3 mb-3">
            <a href="Current_Courses.php" class="back-btn">← Back to Current Courses</a>
        </div>
        <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide font-semibold">
            <a href="Current_Courses.php" class="hover:text-blue-600">Current Courses</a>
            <span class="mx-1">›</span>
            <span class="text-gray-800">
                My Visited Modules<?= $certification_name ? ' · ' . htmlspecialchars($certification_name) : '' ?>
            </span>
        </div>

        <!-- Header -->
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-800">
                📚 My Visited Modules
                <?php if ($certification_name): ?>
                    <span class="text-gray-500 text-lg">· <?= htmlspecialchars($certification_name) ?></span>
                <?php endif; ?>
            </h1>
            <p class="text-sm text-gray-500 mt-1">Only modules you've opened before appear here.</p>
        </div>

        <!-- Notice: this is a history view, not a catalogue. -->
        <div class="notice mb-5">
            <strong>ℹ️ How this works</strong><br>
            This page shows only modules you have opened before. To access a course you haven't visited yet,
            go back to <a href="Current_Courses.php" class="underline font-semibold">Current Courses</a>
            and open it through the <strong>Certification</strong> or <strong>Promotion</strong> side.
        </div>

        <?php if ($visit_count === 0): ?>
            <div class="empty-card">
                <h2 class="text-lg font-bold mb-2">No modules visited yet</h2>
                <p class="text-sm text-gray-600">
                    Once you open a module from the Certification or Promotion side, it will appear here for quick access.
                </p>
                <a href="Current_Courses.php" class="inline-block mt-4 px-4 py-2 bg-blue-500 text-white font-bold rounded hover:bg-blue-600">
                    Go to Current Courses
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto bg-white rounded shadow-sm border border-gray-200">
                <table class="min-w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border-b px-4 py-2 text-sm text-left">#</th>
                            <th class="border-b px-4 py-2 text-sm text-left">Module Code</th>
                            <th class="border-b px-4 py-2 text-sm text-left">Module Name</th>
                            <?php if ($CERTIFICATE === 0): ?>
                                <th class="border-b px-4 py-2 text-sm text-left">Certification</th>
                            <?php endif; ?>
                            <th class="border-b px-4 py-2 text-sm text-center">Visits</th>
                            <th class="border-b px-4 py-2 text-sm text-left">Last Opened</th>
                            <th class="border-b px-4 py-2 text-sm text-center">Status</th>
                            <th class="border-b px-4 py-2 text-sm text-center">Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $row_n = 0; while ($v = $visits->fetch_assoc()): $row_n++;
                            $name = ($LANG === 'French' || $LANG === 'FR') && !empty($v['course_french'])
                                ? $v['course_french']
                                : ($v['course_name'] ?? '(module removed)');
                            $is_active = ($v['course_status'] ?? '') === 'Active';
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border-b px-4 py-2 text-sm"><?= $row_n ?></td>
                                <td class="border-b px-4 py-2 text-sm font-mono"><?= htmlspecialchars($v['course_code'] ?? '—') ?></td>
                                <td class="border-b px-4 py-2 text-sm"><?= htmlspecialchars($name) ?></td>
                                <?php if ($CERTIFICATE === 0): ?>
                                    <td class="border-b px-4 py-2 text-sm"><?= htmlspecialchars($v['certification_name'] ?? '—') ?></td>
                                <?php endif; ?>
                                <td class="border-b px-4 py-2 text-sm text-center"><?= (int)$v['visit_count'] ?></td>
                                <td class="border-b px-4 py-2 text-sm">
                                    <?= htmlspecialchars(date('M d, Y H:i', strtotime($v['last_visited_at']))) ?>
                                </td>
                                <td class="border-b px-4 py-2 text-sm text-center">
                                    <?php if ($is_active): ?>
                                        <i class="fas fa-unlock text-green-500"></i>
                                    <?php else: ?>
                                        <i class="fas fa-lock text-red-500"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="border-b px-4 py-2 text-sm text-center">
                                    <?php if ($v['course_code']): ?>
                                        <a href="Module_topics?COURSE=<?= (int)$v['course_id'] ?>&CERTIFICATE=<?= (int)$v['certification_id'] ?>&LANG=<?= htmlspecialchars($LANG) ?>"
                                           class="inline-block px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs font-bold">
                                            Re-open
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">removed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
    .back-btn {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 14px; border-radius:8px;
        background:#fff; color:#1f2937;
        border:1px solid #d1d5db;
        font-size:13px; font-weight:700; text-decoration:none;
        transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .back-btn:hover { transform:translateX(-2px); border-color:#3b82f6; color:#3b82f6; box-shadow:0 4px 10px rgba(0,0,0,.06); }

    .notice {
        background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a;
        padding:12px 16px; border-radius:8px; font-size:13px;
    }
    .notice a { color:#1d4ed8; }

    .empty-card {
        background:#fff; border:1px dashed #d1d5db; border-radius:10px;
        padding:32px 24px; text-align:center; color:#4b5563;
    }
</style>

<?php
$vstmt->close();
include('footer.php');
?>
<script src="../../main.js"></script>
</body>
</html>
