<?php
/**
 * Laboratory Tools & Equipment — catalogue of every physical tool/component
 * used across the robotics & electronics curriculum (Arduino boards, sensors,
 * motors, soldering tools, robotics kits, power supplies …).
 *
 * Data source: laboratory_equipments  ← Equipment_categories / _sub_categories.
 * Only rows with status = 'Active' are shown. Items have no real photos in the
 * DB (placeholder "YYY"), so each item is rendered with a category icon.
 *
 * Read-only listing with a search box and category filter chips. Reached from
 * the "Laboratory Tools" link in Laboratory_side_bar.php.
 */
include('header.php');

/* --- Filters from the query string --------------------------------------- */
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$cat    = isset($_GET['cat']) && is_numeric($_GET['cat']) ? (int)$_GET['cat'] : 0;

/* --- Category chips (only categories that actually have active items) ----- */
$cat_sql = "SELECT cat.category_id, cat.category_name, COUNT(*) AS n
              FROM laboratory_equipments e
              JOIN Equipment_categories cat ON e.equipments_category = cat.category_id
             WHERE e.equipments_status = 'Active'
             GROUP BY cat.category_id
             ORDER BY n DESC";
$cat_res = mysqli_query($conn, $cat_sql);
$categories = [];
$total_items = 0;
while ($cr = mysqli_fetch_assoc($cat_res)) {
    $categories[] = $cr;
    $total_items += (int)$cr['n'];
}

/* --- Main equipment query ------------------------------------------------- */
$where = "e.equipments_status = 'Active'";
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND (e.equipments_name LIKE '%$s%'
                 OR e.equipments_ModelNo LIKE '%$s%'
                 OR e.equipments_description LIKE '%$s%'
                 OR sub.subcategory_name LIKE '%$s%'
                 OR cat.category_name LIKE '%$s%')";
}
if ($cat > 0) {
    $where .= " AND e.equipments_category = " . $cat;
}

$sql = "SELECT e.equipments_id, e.equipments_name, e.equipments_ModelNo,
               e.equipments_description, e.equipments_category, e.equipments_datasheet,
               cat.category_name, sub.subcategory_name
          FROM laboratory_equipments e
          LEFT JOIN Equipment_categories cat ON e.equipments_category = cat.category_id
          LEFT JOIN Equipment_sub_categories sub ON e.equipments_subcategory = sub.subcategory_id
         WHERE $where
         ORDER BY cat.category_name ASC, e.equipments_name ASC";
$res = mysqli_query($conn, $sql);

/* Group rows by category so each renders as its own section. */
$grouped = [];
$result_count = 0;
while ($r = mysqli_fetch_assoc($res)) {
    $key = $r['category_name'] ?: 'Other Equipment';
    $grouped[$key][] = $r;
    $result_count++;
}

/* Map a category name to a Font Awesome icon. */
function lab_icon($name) {
    $map = [
        'Microcontrollers & Development Boards' => 'fa-microchip',
        'Tools & Equipment'        => 'fa-tools',
        'Robotics Kits'            => 'fa-robot',
        'Cables & Connectors'      => 'fa-plug',
        'Sensors'                  => 'fa-satellite-dish',
        'Motors & Actuators'       => 'fa-cog',
        'Motor Drivers & Controllers' => 'fa-sliders-h',
        'Batteries and Power Supplies' => 'fa-battery-full',
        'Displays'                 => 'fa-tv',
        'Communication Modules'    => 'fa-wifi',
        'Electronics Components'   => 'fa-bolt',
        'Accessories'              => 'fa-toolbox',
    ];
    return isset($map[$name]) ? $map[$name] : 'fa-cube';
}
?>

<div class="flex flex-1">
    <?php include('Laboratory_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">

        <!-- Heading -->
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-800">🧰 Laboratory Tools &amp; Equipment</h1>
            <p class="text-sm text-gray-500 mt-1">
                All physical tools, boards, sensors and kits used across the robotics &amp; electronics curriculum.
            </p>
        </div>

        <!-- Stats -->
        <div class="lab-stats">
            <div class="lab-stat">
                <span class="lab-stat-num"><?= (int)$total_items ?></span>
                <span class="lab-stat-label">Active Items</span>
            </div>
            <div class="lab-stat">
                <span class="lab-stat-num"><?= count($categories) ?></span>
                <span class="lab-stat-label">Categories</span>
            </div>
            <div class="lab-stat">
                <span class="lab-stat-num"><?= (int)$result_count ?></span>
                <span class="lab-stat-label">Showing</span>
            </div>
        </div>

        <!-- Search -->
        <form method="GET" action="Laboratory_Tools_and_Equipments" class="lab-search">
            <?php if ($cat > 0): ?><input type="hidden" name="cat" value="<?= $cat ?>"><?php endif; ?>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by name, model number, category…" class="lab-search-input">
            <button type="submit" class="lab-search-btn"><i class="fas fa-search"></i> Search</button>
            <?php if ($search !== '' || $cat > 0): ?>
                <a href="Laboratory_Tools_and_Equipments" class="lab-clear-btn">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Category chips -->
        <div class="lab-chips">
            <a href="Laboratory_Tools_and_Equipments<?= $search !== '' ? '?q='.urlencode($search) : '' ?>"
               class="lab-chip <?= $cat === 0 ? 'lab-chip-active' : '' ?>">All</a>
            <?php foreach ($categories as $cc):
                $href = 'Laboratory_Tools_and_Equipments?cat=' . (int)$cc['category_id']
                      . ($search !== '' ? '&q=' . urlencode($search) : '');
            ?>
                <a href="<?= $href ?>"
                   class="lab-chip <?= $cat === (int)$cc['category_id'] ? 'lab-chip-active' : '' ?>">
                    <i class="fas <?= lab_icon($cc['category_name']) ?>"></i>
                    <?= htmlspecialchars($cc['category_name']) ?>
                    <span class="lab-chip-count"><?= (int)$cc['n'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Equipment -->
        <?php if (empty($grouped)): ?>
            <div class="empty-card">
                <h2 class="text-lg font-bold mb-2">No equipment found</h2>
                <p class="text-sm text-gray-600">
                    <?php if ($search !== ''): ?>
                        Nothing matches “<?= htmlspecialchars($search) ?>”. Try a different search.
                    <?php else: ?>
                        No active equipment is configured yet.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($grouped as $catName => $items): ?>
                <div class="lab-section">
                    <div class="lab-section-head">
                        <i class="fas <?= lab_icon($catName) ?>"></i>
                        <h2><?= htmlspecialchars($catName) ?></h2>
                        <span class="lab-section-count"><?= count($items) ?></span>
                    </div>
                    <div class="lab-grid">
                        <?php foreach ($items as $it): ?>
                            <div class="lab-item">
                                <div class="lab-item-icon"><i class="fas <?= lab_icon($catName) ?>"></i></div>
                                <div class="lab-item-body">
                                    <div class="lab-item-name"><?= htmlspecialchars($it['equipments_name']) ?></div>
                                    <?php if (!empty($it['equipments_ModelNo']) && trim($it['equipments_ModelNo']) !== ''): ?>
                                        <span class="lab-item-model"><?= htmlspecialchars(trim($it['equipments_ModelNo'])) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($it['subcategory_name'])): ?>
                                        <span class="lab-item-sub"><?= htmlspecialchars($it['subcategory_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($it['equipments_description'])): ?>
                                        <p class="lab-item-desc"><?= htmlspecialchars(mb_strimwidth($it['equipments_description'], 0, 160, '…')) ?></p>
                                    <?php endif; ?>
                                    <?php
                                        /* Curated datasheet/guide link if we have one; otherwise a
                                         * search link so EVERY item still has a working reference. */
                                        $ds = isset($it['equipments_datasheet']) ? trim($it['equipments_datasheet']) : '';
                                        $curated = ($ds !== '');
                                        if (!$curated) {
                                            $terms = trim($it['equipments_name']) . ' ' . trim($it['equipments_ModelNo']) . ' datasheet';
                                            $ds = 'https://www.google.com/search?q=' . urlencode($terms);
                                        }
                                    ?>
                                    <a href="<?= htmlspecialchars($ds) ?>" target="_blank" rel="noopener noreferrer"
                                       class="lab-item-ds <?= $curated ? '' : 'lab-item-ds-search' ?>">
                                        <i class="fas <?= $curated ? 'fa-file-pdf' : 'fa-search' ?>"></i>
                                        <?= $curated ? 'Datasheet / Guide' : 'Find datasheet' ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>

<style>
    .lab-stats { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:18px; }
    .lab-stat {
        background:#fff; border:1px solid #e5e7eb; border-radius:10px;
        padding:14px 22px; min-width:120px; box-shadow:0 1px 3px rgba(0,0,0,.04);
        display:flex; flex-direction:column;
    }
    .lab-stat-num { font-size:24px; font-weight:800; color:#1e3a8a; line-height:1; }
    .lab-stat-label { font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:#6b7280; margin-top:6px; }

    .lab-search { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
    .lab-search-input {
        flex:1; min-width:220px; padding:10px 14px; border:1px solid #d1d5db;
        border-radius:8px; font-size:14px; outline:none;
    }
    .lab-search-input:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
    .lab-search-btn {
        padding:10px 18px; border-radius:8px; background:#1d4ed8; color:#fff;
        font-size:13px; font-weight:700; border:none; cursor:pointer;
    }
    .lab-search-btn:hover { background:#1e40af; }
    .lab-clear-btn {
        padding:10px 16px; border-radius:8px; background:#fff; color:#374151;
        border:1px solid #d1d5db; font-size:13px; font-weight:700; text-decoration:none;
        display:inline-flex; align-items:center;
    }
    .lab-clear-btn:hover { border-color:#ef4444; color:#ef4444; }

    .lab-chips { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px; }
    .lab-chip {
        display:inline-flex; align-items:center; gap:6px;
        padding:6px 12px; border-radius:99px; background:#fff;
        border:1px solid #e5e7eb; color:#374151; font-size:12px; font-weight:700;
        text-decoration:none; transition:all .12s ease;
    }
    .lab-chip:hover { border-color:#3b82f6; color:#1d4ed8; }
    .lab-chip i { font-size:11px; color:#9ca3af; }
    .lab-chip-active { background:#1d4ed8; border-color:#1d4ed8; color:#fff; }
    .lab-chip-active i { color:#bfdbfe; }
    .lab-chip-count {
        font-size:10px; background:rgba(0,0,0,.08); padding:1px 6px; border-radius:99px;
    }
    .lab-chip-active .lab-chip-count { background:rgba(255,255,255,.25); }

    .lab-section { margin-bottom:26px; }
    .lab-section-head {
        display:flex; align-items:center; gap:10px; margin-bottom:12px;
        padding-bottom:8px; border-bottom:2px solid #e5e7eb;
    }
    .lab-section-head i { color:#1d4ed8; font-size:18px; }
    .lab-section-head h2 { font-size:16px; font-weight:800; color:#1e3a8a; }
    .lab-section-count {
        font-size:11px; font-weight:700; background:#eff6ff; color:#1d4ed8;
        padding:2px 9px; border-radius:99px; border:1px solid #bfdbfe;
    }

    .lab-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:12px; }
    .lab-item {
        display:flex; gap:12px; background:#fff; border:1px solid #e5e7eb;
        border-radius:10px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,.04);
        transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .lab-item:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,.08); border-color:#bfdbfe; }
    .lab-item-icon {
        flex:none; width:44px; height:44px; border-radius:10px;
        background:linear-gradient(135deg,#eff6ff,#dbeafe); color:#1d4ed8;
        display:flex; align-items:center; justify-content:center; font-size:18px;
    }
    .lab-item-body { min-width:0; }
    .lab-item-name { font-size:14px; font-weight:700; color:#111827; }
    .lab-item-model {
        display:inline-block; font-family:'Courier New',monospace; font-size:10px; font-weight:700;
        color:#6b7280; background:#f3f4f6; padding:1px 6px; border-radius:4px; margin-top:4px; margin-right:4px;
    }
    .lab-item-sub {
        display:inline-block; font-size:10px; font-weight:700; color:#047857;
        background:#ecfdf5; padding:1px 7px; border-radius:99px; margin-top:4px; border:1px solid #a7f3d0;
    }
    .lab-item-desc { font-size:12px; color:#6b7280; margin-top:7px; line-height:1.4; }
    .lab-item-ds {
        display:inline-flex; align-items:center; gap:6px; margin-top:10px;
        padding:6px 11px; border-radius:7px; font-size:12px; font-weight:700;
        text-decoration:none; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;
        transition:all .12s ease;
    }
    .lab-item-ds:hover { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
    .lab-item-ds-search { background:#f9fafb; color:#6b7280; border-color:#e5e7eb; }
    .lab-item-ds-search:hover { background:#374151; color:#fff; border-color:#374151; }

    .empty-card {
        background:#fff; border:1px dashed #d1d5db; border-radius:10px;
        padding:32px 24px; text-align:center; color:#4b5563;
    }
</style>

<?php include('footer.php'); ?>
<script src="../../main.js"></script>
</body>
</html>
