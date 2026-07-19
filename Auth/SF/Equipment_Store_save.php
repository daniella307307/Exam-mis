<?php
/**
 * Equipment_Store_save.php — add / update / delete rows in a facilitator's own
 * equipment store. Every row is scoped to owner_user_id = the logged-in
 * facilitator, so each facilitator's inventory is completely independent
 * (just like exams). All writes are prepared statements gated by that owner id.
 *
 * Posts back to Equipment_Store with ?msg=... so the page can flash a result.
 */
include('session.php');

$owner  = (int)($_SESSION['user_id'] ?? 0);
$school = (int)($school_ref ?? 0);

function store_redirect($msg) {
    header('Location: Equipment_Store?msg=' . urlencode($msg));
    exit;
}

if ($owner <= 0) { store_redirect('error'); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { store_redirect('error'); }

$action = $_POST['action'] ?? '';

/* Normalise stock status against the quantity: 0 on hand is always
 * "out of stock"; a positive quantity keeps whatever the user picked
 * (in_stock / low_stock), defaulting to in_stock. */
function normalise_status($qty, $picked) {
    $qty = (int)$qty;
    if ($qty <= 0) return 'out_of_stock';
    return in_array($picked, ['in_stock', 'low_stock'], true) ? $picked : 'in_stock';
}

if ($action === 'add') {
    $equipment_id = isset($_POST['equipment_id']) ? (int)$_POST['equipment_id'] : 0;
    $custom_name  = trim($_POST['custom_name'] ?? '');
    $category_id  = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $model_no     = trim($_POST['model_no'] ?? '');
    $quantity     = max(0, (int)($_POST['quantity'] ?? 0));
    $location     = trim($_POST['location'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');
    $status       = normalise_status($quantity, $_POST['stock_status'] ?? '');

    $item_name = $custom_name;

    /* If a catalogue item was chosen, pull its authoritative name/category/model. */
    if ($equipment_id > 0) {
        $q = $conn->prepare("SELECT equipments_name, equipments_ModelNo, equipments_category
                               FROM laboratory_equipments WHERE equipments_id = ?");
        $q->bind_param('i', $equipment_id);
        $q->execute();
        $cat = $q->get_result()->fetch_assoc();
        $q->close();
        if ($cat) {
            $item_name   = $cat['equipments_name'];
            if ($model_no === '') $model_no = (string)$cat['equipments_ModelNo'];
            if ($category_id === null && (int)$cat['equipments_category'] > 0) $category_id = (int)$cat['equipments_category'];
        }
    } else {
        $equipment_id = null; // custom item
    }

    if ($item_name === '') { store_redirect('need_name'); }

    $stmt = $conn->prepare("INSERT INTO facilitator_equipment_stock
        (owner_user_id, school_id, equipment_id, item_name, category_id, model_no, quantity, stock_status, location, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iiisisssss',
        $owner, $school, $equipment_id, $item_name, $category_id, $model_no, $quantity, $status, $location, $notes);
    $ok = $stmt->execute();
    $stmt->close();
    store_redirect($ok ? 'added' : 'error');
}

if ($action === 'update') {
    $stock_id = (int)($_POST['stock_id'] ?? 0);
    $quantity = max(0, (int)($_POST['quantity'] ?? 0));
    $location = trim($_POST['location'] ?? '');
    $notes    = trim($_POST['notes'] ?? '');
    $status   = normalise_status($quantity, $_POST['stock_status'] ?? '');

    if ($stock_id <= 0) { store_redirect('error'); }

    $stmt = $conn->prepare("UPDATE facilitator_equipment_stock
                               SET quantity = ?, stock_status = ?, location = ?, notes = ?
                             WHERE stock_id = ? AND owner_user_id = ?");
    $stmt->bind_param('isssii', $quantity, $status, $location, $notes, $stock_id, $owner);
    $ok = $stmt->execute();
    $stmt->close();
    store_redirect($ok ? 'updated' : 'error');
}

if ($action === 'delete') {
    $stock_id = (int)($_POST['stock_id'] ?? 0);
    if ($stock_id <= 0) { store_redirect('error'); }

    $stmt = $conn->prepare("DELETE FROM facilitator_equipment_stock
                             WHERE stock_id = ? AND owner_user_id = ?");
    $stmt->bind_param('ii', $stock_id, $owner);
    $ok = $stmt->execute();
    $stmt->close();
    store_redirect($ok ? 'deleted' : 'error');
}

store_redirect('error');
