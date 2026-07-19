<?php
/**
 * Equipment_Store.php — a facilitator's OWN lab equipment store / inventory.
 *
 * Independent per facilitator: every row is scoped to owner_user_id = the
 * logged-in user (see facilitator_equipment_stock), so each facilitator tracks
 * their own stock — what they have, quantities, and what is out of stock —
 * exactly the way exams are independent per teacher.
 *
 * Writes go through Equipment_Store_save.php. This page only reads + renders.
 */
include('header.php');

$owner  = (int)($_SESSION['user_id'] ?? 0);

/* --- Categories (for the add form + grouping) ---------------------------- */
$categories = [];
$cres = mysqli_query($conn, "SELECT category_id, category_name FROM Equipment_categories
                              WHERE category_status = 'Active' ORDER BY category_name ASC");
while ($c = mysqli_fetch_assoc($cres)) { $categories[(int)$c['category_id']] = $c['category_name']; }

/* --- Catalogue items for the "pick from catalogue" dropdown --------------- */
$catalogue = [];
$ares = mysqli_query($conn, "SELECT equipments_id, equipments_name, equipments_ModelNo, equipments_category
                               FROM laboratory_equipments
                              WHERE equipments_status = 'Active'
                              ORDER BY equipments_name ASC");
while ($a = mysqli_fetch_assoc($ares)) { $catalogue[] = $a; }

/* --- This facilitator's own stock ---------------------------------------- */
$stock = [];
$stmt = $conn->prepare("SELECT s.*, c.category_name
                          FROM facilitator_equipment_stock s
                          LEFT JOIN Equipment_categories c ON s.category_id = c.category_id
                         WHERE s.owner_user_id = ?
                         ORDER BY c.category_name ASC, s.item_name ASC");
$stmt->bind_param('i', $owner);
$stmt->execute();
$sres = $stmt->get_result();

$grouped = [];
$stat_total = 0; $stat_in = 0; $stat_low = 0; $stat_out = 0; $stat_qty = 0;
while ($r = $sres->fetch_assoc()) {
    $key = $r['category_name'] ?: 'Uncategorised';
    $grouped[$key][] = $r;
    $stat_total++;
    $stat_qty += (int)$r['quantity'];
    if ($r['stock_status'] === 'out_of_stock')      $stat_out++;
    elseif ($r['stock_status'] === 'low_stock')     $stat_low++;
    else                                            $stat_in++;
}
$stmt->close();

function status_badge($s) {
    switch ($s) {
        case 'out_of_stock': return '<span class="st-badge st-out"><i class="fas fa-times-circle"></i> Out of stock</span>';
        case 'low_stock':    return '<span class="st-badge st-low"><i class="fas fa-exclamation-triangle"></i> Low stock</span>';
        default:             return '<span class="st-badge st-in"><i class="fas fa-check-circle"></i> In stock</span>';
    }
}
$msg = $_GET['msg'] ?? '';
$msg_map = [
    'added'     => ['ok',  'Item added to your store.'],
    'updated'   => ['ok',  'Stock updated.'],
    'deleted'   => ['ok',  'Item removed from your store.'],
    'need_name' => ['err', 'Please choose a catalogue item or type a custom name.'],
    'error'     => ['err', 'Something went wrong. Please try again.'],
];
?>

<div class="flex flex-1">
    <?php include('Laboratory_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">

        <!-- Heading -->
        <div class="mb-5">
            <span class="icrp-brand"><i class="fas fa-cubes"></i> ICRPplus</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">📦 My Equipment Store</h1>
            <p class="text-sm text-gray-500 mt-1">
                Your own independent inventory — track what you have, quantities, and what is out of stock.
                This store belongs only to <strong><?= htmlspecialchars(trim(($user_data['firstname'] ?? '').' '.($user_data['lastname'] ?? ''))) ?></strong>.
            </p>
        </div>

        <?php if ($msg !== '' && isset($msg_map[$msg])): ?>
            <div class="flash flash-<?= $msg_map[$msg][0] ?>"><?= htmlspecialchars($msg_map[$msg][1]) ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="lab-stats">
            <div class="lab-stat"><span class="lab-stat-num"><?= $stat_total ?></span><span class="lab-stat-label">Items</span></div>
            <div class="lab-stat"><span class="lab-stat-num"><?= $stat_qty ?></span><span class="lab-stat-label">Total Qty</span></div>
            <div class="lab-stat"><span class="lab-stat-num" style="color:#047857"><?= $stat_in ?></span><span class="lab-stat-label">In Stock</span></div>
            <div class="lab-stat"><span class="lab-stat-num" style="color:#b45309"><?= $stat_low ?></span><span class="lab-stat-label">Low Stock</span></div>
            <div class="lab-stat"><span class="lab-stat-num" style="color:#b91c1c"><?= $stat_out ?></span><span class="lab-stat-label">Out of Stock</span></div>
        </div>

        <button type="button" class="add-btn" onclick="document.getElementById('addModal').style.display='flex'">
            <i class="fas fa-plus"></i> Add equipment
        </button>

        <!-- Inventory -->
        <?php if (empty($grouped)): ?>
            <div class="empty-card">
                <h2 class="text-lg font-bold mb-2">Your store is empty</h2>
                <p class="text-sm text-gray-600">Click <strong>Add equipment</strong> to record what you have in your lab.</p>
            </div>
        <?php else: ?>
            <?php foreach ($grouped as $catName => $items): ?>
                <div class="lab-section">
                    <div class="lab-section-head">
                        <i class="fas fa-layer-group"></i>
                        <h2><?= htmlspecialchars($catName) ?></h2>
                        <span class="lab-section-count"><?= count($items) ?></span>
                    </div>
                    <div class="store-grid">
                        <?php foreach ($items as $it): ?>
                            <div class="store-item">
                                <div class="store-item-head">
                                    <div class="store-item-name"><?= htmlspecialchars($it['item_name']) ?></div>
                                    <?= status_badge($it['stock_status']) ?>
                                </div>
                                <?php if (!empty($it['model_no'])): ?>
                                    <span class="lab-item-model"><?= htmlspecialchars($it['model_no']) ?></span>
                                <?php endif; ?>
                                <div class="store-qty">Qty: <strong><?= (int)$it['quantity'] ?></strong></div>
                                <?php if (!empty($it['location'])): ?>
                                    <div class="store-meta"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($it['location']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($it['notes'])): ?>
                                    <div class="store-notes"><?= htmlspecialchars($it['notes']) ?></div>
                                <?php endif; ?>
                                <div class="store-actions">
                                    <button type="button" class="mini-btn mini-edit"
                                        onclick='openEdit(<?= json_encode([
                                            "id"=>(int)$it["stock_id"],
                                            "name"=>$it["item_name"],
                                            "qty"=>(int)$it["quantity"],
                                            "status"=>$it["stock_status"],
                                            "location"=>$it["location"],
                                            "notes"=>$it["notes"],
                                        ], JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                    <form method="POST" action="Equipment_Store_save.php" class="inline-form"
                                          onsubmit="return confirm('Remove this item from your store?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="stock_id" value="<?= (int)$it['stock_id'] ?>">
                                        <button type="submit" class="mini-btn mini-del"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<!-- Add modal -->
<div class="modal-backdrop" id="addModal">
    <div class="modal">
        <div class="modal-head"><h3>Add equipment to your store</h3>
            <button type="button" class="modal-x" onclick="document.getElementById('addModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="Equipment_Store_save.php">
            <input type="hidden" name="action" value="add">
            <label class="fld-label">Pick from the lab catalogue</label>
            <select name="equipment_id" class="fld" onchange="catPick(this)">
                <option value="0">— Choose a catalogue item —</option>
                <?php foreach ($catalogue as $c): ?>
                    <option value="<?= (int)$c['equipments_id'] ?>" data-cat="<?= (int)$c['equipments_category'] ?>">
                        <?= htmlspecialchars($c['equipments_name']) ?><?= trim($c['equipments_ModelNo']) !== '' ? ' ('.htmlspecialchars(trim($c['equipments_ModelNo'])).')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="fld-label">…or type a custom item name</label>
            <input type="text" name="custom_name" class="fld" placeholder="e.g. LEGP Robotics Kit, homemade sensor rig…">

            <div class="fld-row">
                <div style="flex:1">
                    <label class="fld-label">Category</label>
                    <select name="category_id" id="catSelect" class="fld">
                        <option value="">— Select —</option>
                        <?php foreach ($categories as $cid => $cname): ?>
                            <option value="<?= $cid ?>"><?= htmlspecialchars($cname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="width:110px">
                    <label class="fld-label">Quantity</label>
                    <input type="number" name="quantity" class="fld" min="0" value="1">
                </div>
            </div>

            <div class="fld-row">
                <div style="flex:1">
                    <label class="fld-label">Status</label>
                    <select name="stock_status" class="fld">
                        <option value="in_stock">In stock</option>
                        <option value="low_stock">Low stock</option>
                        <option value="out_of_stock">Out of stock</option>
                    </select>
                </div>
                <div style="flex:1">
                    <label class="fld-label">Location (optional)</label>
                    <input type="text" name="location" class="fld" placeholder="Shelf / cupboard">
                </div>
            </div>

            <label class="fld-label">Notes (optional)</label>
            <textarea name="notes" class="fld" rows="2" placeholder="Condition, missing parts, supplier…"></textarea>

            <div class="modal-foot">
                <button type="button" class="btn-ghost" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save to my store</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit modal -->
<div class="modal-backdrop" id="editModal">
    <div class="modal">
        <div class="modal-head"><h3>Update stock</h3>
            <button type="button" class="modal-x" onclick="document.getElementById('editModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="Equipment_Store_save.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="stock_id" id="e_id">
            <p class="edit-name" id="e_name"></p>
            <div class="fld-row">
                <div style="width:120px">
                    <label class="fld-label">Quantity</label>
                    <input type="number" name="quantity" id="e_qty" class="fld" min="0">
                </div>
                <div style="flex:1">
                    <label class="fld-label">Status</label>
                    <select name="stock_status" id="e_status" class="fld">
                        <option value="in_stock">In stock</option>
                        <option value="low_stock">Low stock</option>
                        <option value="out_of_stock">Out of stock</option>
                    </select>
                </div>
            </div>
            <label class="fld-label">Location</label>
            <input type="text" name="location" id="e_location" class="fld">
            <label class="fld-label">Notes</label>
            <textarea name="notes" id="e_notes" class="fld" rows="2"></textarea>
            <div class="modal-foot">
                <button type="button" class="btn-ghost" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<style>
    .icrp-brand {
        display:inline-flex; align-items:center; gap:7px; font-weight:800; font-size:12px;
        letter-spacing:.5px; color:#1d4ed8; background:linear-gradient(135deg,#eff6ff,#dbeafe);
        border:1px solid #bfdbfe; padding:4px 12px; border-radius:99px; text-transform:uppercase;
    }
    .lab-stats { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:18px; }
    .lab-stat { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:14px 22px; min-width:110px; box-shadow:0 1px 3px rgba(0,0,0,.04); display:flex; flex-direction:column; }
    .lab-stat-num { font-size:24px; font-weight:800; color:#1e3a8a; line-height:1; }
    .lab-stat-label { font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:#6b7280; margin-top:6px; }

    .flash { padding:11px 16px; border-radius:9px; font-size:13px; font-weight:700; margin-bottom:16px; }
    .flash-ok { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
    .flash-err { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }

    .add-btn { display:inline-flex; align-items:center; gap:8px; background:#1d4ed8; color:#fff; border:none; padding:10px 18px; border-radius:9px; font-size:13px; font-weight:700; cursor:pointer; margin-bottom:20px; }
    .add-btn:hover { background:#1e40af; }

    .lab-section { margin-bottom:26px; }
    .lab-section-head { display:flex; align-items:center; gap:10px; margin-bottom:12px; padding-bottom:8px; border-bottom:2px solid #e5e7eb; }
    .lab-section-head i { color:#1d4ed8; font-size:18px; }
    .lab-section-head h2 { font-size:16px; font-weight:800; color:#1e3a8a; }
    .lab-section-count { font-size:11px; font-weight:700; background:#eff6ff; color:#1d4ed8; padding:2px 9px; border-radius:99px; border:1px solid #bfdbfe; }

    .store-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:12px; }
    .store-item { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    .store-item-head { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; }
    .store-item-name { font-size:14px; font-weight:700; color:#111827; }
    .lab-item-model { display:inline-block; font-family:'Courier New',monospace; font-size:10px; font-weight:700; color:#6b7280; background:#f3f4f6; padding:1px 6px; border-radius:4px; margin-top:6px; }
    .store-qty { font-size:13px; color:#374151; margin-top:8px; }
    .store-meta { font-size:11px; color:#6b7280; margin-top:6px; }
    .store-notes { font-size:12px; color:#6b7280; margin-top:6px; font-style:italic; }
    .store-actions { display:flex; gap:8px; margin-top:12px; align-items:center; }
    .inline-form { display:inline; }
    .mini-btn { border:none; cursor:pointer; padding:6px 11px; border-radius:7px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px; }
    .mini-edit { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .mini-edit:hover { background:#1d4ed8; color:#fff; }
    .mini-del { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
    .mini-del:hover { background:#b91c1c; color:#fff; }

    .st-badge { display:inline-flex; align-items:center; gap:5px; white-space:nowrap; font-size:10px; font-weight:800; padding:3px 9px; border-radius:99px; text-transform:uppercase; letter-spacing:.3px; }
    .st-in  { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
    .st-low { background:#fffbeb; color:#b45309; border:1px solid #fde68a; }
    .st-out { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }

    .empty-card { background:#fff; border:1px dashed #d1d5db; border-radius:10px; padding:32px 24px; text-align:center; color:#4b5563; }

    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:60; align-items:flex-start; justify-content:center; padding:40px 16px; overflow-y:auto; }
    .modal { background:#fff; border-radius:14px; width:100%; max-width:520px; box-shadow:0 20px 50px rgba(0,0,0,.3); }
    .modal-head { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #eef2f7; }
    .modal-head h3 { font-size:16px; font-weight:800; color:#1e293b; }
    .modal-x { border:none; background:none; font-size:26px; line-height:1; color:#94a3b8; cursor:pointer; }
    .modal form { padding:18px 20px; }
    .fld-label { display:block; font-size:12px; font-weight:700; color:#475569; margin:12px 0 5px; }
    .fld { width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; }
    .fld:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
    .fld-row { display:flex; gap:12px; }
    .edit-name { font-weight:800; color:#1e293b; margin:4px 0 8px; }
    .modal-foot { display:flex; justify-content:flex-end; gap:10px; margin-top:18px; }
    .btn-ghost { background:#fff; border:1px solid #d1d5db; color:#374151; padding:9px 16px; border-radius:8px; font-weight:700; font-size:13px; cursor:pointer; }
    .btn-primary { background:#1d4ed8; border:none; color:#fff; padding:9px 18px; border-radius:8px; font-weight:700; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
    .btn-primary:hover { background:#1e40af; }
</style>

<script>
    // When a catalogue item is chosen, mirror its category into the category select.
    function catPick(sel) {
        var opt = sel.options[sel.selectedIndex];
        var cat = opt.getAttribute('data-cat');
        if (cat && cat !== '0') { document.getElementById('catSelect').value = cat; }
    }
    function openEdit(d) {
        document.getElementById('e_id').value = d.id;
        document.getElementById('e_name').textContent = d.name;
        document.getElementById('e_qty').value = d.qty;
        document.getElementById('e_status').value = d.status;
        document.getElementById('e_location').value = d.location || '';
        document.getElementById('e_notes').value = d.notes || '';
        document.getElementById('editModal').style.display = 'flex';
    }
    // Close modals on backdrop click.
    document.querySelectorAll('.modal-backdrop').forEach(function(bd){
        bd.addEventListener('click', function(e){ if (e.target === bd) bd.style.display='none'; });
    });
</script>

<?php include('footer.php'); ?>
<script src="../../main.js"></script>
</body>
</html>
