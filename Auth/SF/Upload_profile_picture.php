<?php
/**
 * Upload_profile_picture — the logged-in user updates their own avatar.
 *
 * Flow:
 *   1. User picks a photo → Cropper.js shows a 1:1 crop box + zoom/rotate.
 *   2. On save, the cropped 400×400 canvas is serialised to a base64 JPEG and
 *      POSTed in the hidden `cropped_image` field.
 *   3. Server decodes it, RE-ENCODES through GD (which strips any non-image
 *      payload — a security must for user uploads), writes a unique file to
 *      Auth/profiles/, deletes the previous avatar, and updates users.user_image.
 *
 * Falls back to a plain validated file upload if JavaScript is disabled.
 * Profile images are stored as `profiles/<file>` (referenced as ../<value>).
 */
ob_start();
include('header.php'); // provides $conn, $session_id, $user_image, $user_data

$msg = '';
$msgType = ''; // 'ok' | 'err'
$PROFILE_DIR = '../profiles/';           // filesystem dir (relative to Auth/SF)
$MAX_BYTES   = 5 * 1024 * 1024;          // 5 MB ceiling on decoded image

/** Safely delete the user's previous avatar (only inside profiles/). */
function delete_old_avatar($stored) {
    if (!$stored) return;
    $base = basename($stored);                 // strip any path
    if ($base === '' ) return;
    $path = '../profiles/' . $base;
    // Only remove real files that actually live in the profiles folder.
    if (is_file($path)) { @unlink($path); }
}

/** Persist a GD image resource as a 400×400 JPEG, return relative DB path or false. */
function save_avatar($img, $session_id) {
    $size = 400;
    $w = imagesx($img); $h = imagesy($img);
    $canvas = imagecreatetruecolor($size, $size);
    // White backing so any transparency flattens cleanly.
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefilledrectangle($canvas, 0, 0, $size, $size, $white);
    imagecopyresampled($canvas, $img, 0, 0, 0, 0, $size, $size, $w, $h);

    $fname = 'user_' . (int)$session_id . '_' . bin2hex(random_bytes(5)) . '.jpg';
    $abs   = '../profiles/' . $fname;
    $ok    = imagejpeg($canvas, $abs, 90);
    imagedestroy($canvas);
    return $ok ? $fname : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $img = null;

    // --- Primary path: cropped base64 image from Cropper.js -----------------
    if (!empty($_POST['cropped_image'])) {
        $data = $_POST['cropped_image'];
        if (preg_match('#^data:image/(png|jpe?g|webp);base64,#', $data)) {
            $bin = base64_decode(substr($data, strpos($data, ',') + 1), true);
            if ($bin === false || strlen($bin) < 100) {
                $msg = 'The cropped image could not be read. Please try again.'; $msgType = 'err';
            } elseif (strlen($bin) > $MAX_BYTES) {
                $msg = 'Image is too large (max 5 MB). Try a smaller crop.'; $msgType = 'err';
            } else {
                $img = @imagecreatefromstring($bin);
                if (!$img) { $msg = 'That does not look like a valid image.'; $msgType = 'err'; }
            }
        } else {
            $msg = 'Unsupported image format.'; $msgType = 'err';
        }
    }
    // --- Fallback path: plain file upload (JS off) --------------------------
    elseif (!empty($_FILES['uploadfile']['name']) && $_FILES['uploadfile']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['uploadfile']['tmp_name'];
        $info = @getimagesize($tmp); // validates it is a real image
        $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];
        if (!$info || !in_array($info[2], $allowed, true)) {
            $msg = 'Please choose a valid image file (JPG, PNG, WEBP or GIF).'; $msgType = 'err';
        } elseif (filesize($tmp) > $MAX_BYTES) {
            $msg = 'Image is too large (max 5 MB).'; $msgType = 'err';
        } else {
            $img = @imagecreatefromstring(file_get_contents($tmp));
            if (!$img) { $msg = 'The image could not be processed.'; $msgType = 'err'; }
        }
    } else {
        $msg = 'Please choose a photo first.'; $msgType = 'err';
    }

    // --- Save + update DB ---------------------------------------------------
    if ($img) {
        $fname = save_avatar($img, $session_id);
        imagedestroy($img);
        if ($fname) {
            delete_old_avatar($user_image);
            $rel  = 'profiles/' . $fname;
            $safe = mysqli_real_escape_string($conn, $rel);
            $sid  = (int)$session_id;
            if (mysqli_query($conn, "UPDATE users SET user_image = '$safe' WHERE user_id = $sid")) {
                $msg = 'Your profile picture has been updated.'; $msgType = 'ok';
                $user_image = $rel; // reflect immediately in this page's preview
            } else {
                $msg = 'Saved the file but could not update your profile. Try again.'; $msgType = 'err';
            }
        } else {
            $msg = 'Could not write the image to the server. Check folder permissions.'; $msgType = 'err';
        }
    }
}
?>

<link rel="stylesheet" href="../../dist/cropper.min.css">

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">

        <div class="flex items-center gap-3 mb-3">
            <a href="User_profile" class="pp-back">← Back to Profile</a>
        </div>

        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-800">🖼️ Profile Picture</h1>
            <p class="text-sm text-gray-500 mt-1">Upload a photo, crop it to a square, then save.</p>
        </div>

        <?php if ($msg !== ''): ?>
            <div class="pp-alert <?= $msgType === 'ok' ? 'pp-alert-ok' : 'pp-alert-err' ?>">
                <i class="fas <?= $msgType === 'ok' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <span><?= htmlspecialchars($msg) ?></span>
            </div>
            <?php if ($msgType === 'ok'): ?>
                <script>setTimeout(function(){ window.location.href = 'User_profile'; }, 1200);</script>
            <?php endif; ?>
        <?php endif; ?>

        <form id="ppForm" action="Upload_profile_picture" method="POST" enctype="multipart/form-data" class="pp-card">
            <input type="hidden" name="cropped_image" id="cropped_image">

            <div class="pp-grid">
                <!-- Left: current + chooser + editor -->
                <div>
                    <div class="pp-current">
                        <img src="../<?= htmlspecialchars($user_image ?: 'profiles/default.png') ?>"
                             onerror="this.style.visibility='hidden'"
                             class="pp-current-img" alt="Current picture">
                        <div>
                            <div class="pp-current-name"><?= htmlspecialchars(trim(($user_data['firstname'] ?? '').' '.($user_data['lastname'] ?? ''))) ?></div>
                            <div class="pp-current-label">Current picture</div>
                        </div>
                    </div>

                    <label class="pp-file-btn">
                        <i class="fas fa-upload"></i> Choose a photo…
                        <input type="file" id="fileInput" name="uploadfile" accept="image/*" hidden>
                    </label>
                    <p class="pp-hint">JPG, PNG, WEBP or GIF · up to 5 MB</p>

                    <div id="editor" class="pp-editor" style="display:none;">
                        <div class="pp-crop-wrap">
                            <img id="cropImage" alt="To crop">
                        </div>
                        <div class="pp-tools">
                            <button type="button" data-act="zoomin"  title="Zoom in"><i class="fas fa-search-plus"></i></button>
                            <button type="button" data-act="zoomout" title="Zoom out"><i class="fas fa-search-minus"></i></button>
                            <button type="button" data-act="rotl"    title="Rotate left"><i class="fas fa-undo"></i></button>
                            <button type="button" data-act="rotr"    title="Rotate right"><i class="fas fa-redo"></i></button>
                            <button type="button" data-act="reset"   title="Reset"><i class="fas fa-sync"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Right: live round preview -->
                <div class="pp-preview-col">
                    <div class="pp-preview-label">Preview</div>
                    <div id="preview" class="pp-preview"></div>
                    <div class="pp-preview-label" style="margin-top:8px;">Round avatar</div>
                    <div id="previewSmall" class="pp-preview pp-preview-sm"></div>
                </div>
            </div>

            <div class="pp-actions">
                <button type="submit" id="saveBtn" class="pp-save" disabled>
                    <i class="fas fa-save"></i> Save Profile Picture
                </button>
                <a href="User_profile" class="pp-cancel">Cancel</a>
            </div>
        </form>
    </main>
</div>

<style>
    .pp-back {
        display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:8px;
        background:#fff; color:#1f2937; border:1px solid #d1d5db; font-size:13px; font-weight:700;
        text-decoration:none; transition:all .12s ease;
    }
    .pp-back:hover { border-color:#3b82f6; color:#3b82f6; transform:translateX(-2px); }

    .pp-alert {
        display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:10px;
        font-size:14px; font-weight:600; margin-bottom:16px;
    }
    .pp-alert-ok  { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
    .pp-alert-err { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }

    .pp-card {
        background:#fff; border:1px solid #e5e7eb; border-radius:12px;
        padding:22px; box-shadow:0 1px 3px rgba(0,0,0,.04); max-width:820px;
    }
    .pp-grid { display:grid; grid-template-columns:1fr 220px; gap:24px; }
    @media (max-width:720px){ .pp-grid { grid-template-columns:1fr; } }

    .pp-current { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .pp-current-img {
        width:56px; height:56px; border-radius:50%; object-fit:cover;
        border:2px solid #e5e7eb; background:#f3f4f6;
    }
    .pp-current-name { font-weight:800; color:#111827; font-size:15px; }
    .pp-current-label { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#9ca3af; font-weight:700; }

    .pp-file-btn {
        display:inline-flex; align-items:center; gap:8px; cursor:pointer;
        padding:10px 18px; border-radius:8px; background:#1d4ed8; color:#fff;
        font-size:13px; font-weight:700;
    }
    .pp-file-btn:hover { background:#1e40af; }
    .pp-hint { font-size:12px; color:#9ca3af; margin-top:8px; }

    .pp-editor { margin-top:16px; }
    .pp-crop-wrap { max-width:100%; max-height:360px; background:#0f172a; border-radius:10px; overflow:hidden; }
    .pp-crop-wrap img { max-width:100%; display:block; }
    .pp-tools { display:flex; gap:8px; margin-top:10px; flex-wrap:wrap; }
    .pp-tools button {
        width:40px; height:40px; border-radius:8px; border:1px solid #e5e7eb; background:#fff;
        color:#374151; cursor:pointer; font-size:14px; transition:all .12s ease;
    }
    .pp-tools button:hover { border-color:#1d4ed8; color:#1d4ed8; background:#eff6ff; }

    .pp-preview-col { display:flex; flex-direction:column; align-items:center; }
    .pp-preview-label { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#9ca3af; font-weight:700; margin-bottom:8px; }
    .pp-preview {
        width:180px; height:180px; border-radius:12px; overflow:hidden;
        border:1px solid #e5e7eb; background:#f3f4f6;
    }
    .pp-preview-sm { width:72px; height:72px; border-radius:50%; }
    .pp-preview img { display:block; }

    .pp-actions { display:flex; align-items:center; gap:12px; margin-top:22px; padding-top:18px; border-top:1px solid #f1f5f9; }
    .pp-save {
        display:inline-flex; align-items:center; gap:8px; padding:11px 22px; border-radius:9px;
        background:#16a34a; color:#fff; font-size:14px; font-weight:800; border:none; cursor:pointer;
    }
    .pp-save:hover { background:#15803d; }
    .pp-save:disabled { background:#cbd5e1; cursor:not-allowed; }
    .pp-cancel { color:#6b7280; font-size:13px; font-weight:700; text-decoration:none; }
    .pp-cancel:hover { color:#ef4444; }
</style>

<script src="../../dist/cropper.min.js"></script>
<script>
(function () {
    var fileInput = document.getElementById('fileInput');
    var editor    = document.getElementById('editor');
    var image     = document.getElementById('cropImage');
    var saveBtn   = document.getElementById('saveBtn');
    var hidden    = document.getElementById('cropped_image');
    var form      = document.getElementById('ppForm');
    var cropper   = null;

    fileInput.addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (!file) return;
        if (!/^image\//.test(file.type)) { alert('Please choose an image file.'); return; }
        if (file.size > 5 * 1024 * 1024) { alert('Image is too large (max 5 MB).'); return; }

        var reader = new FileReader();
        reader.onload = function (ev) {
            image.src = ev.target.result;
            editor.style.display = 'block';
            if (cropper) cropper.destroy();
            cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
                background: false,
                responsive: true,
                preview: '.pp-preview'
            });
            saveBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    });

    // Toolbar
    document.querySelectorAll('.pp-tools button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!cropper) return;
            switch (btn.getAttribute('data-act')) {
                case 'zoomin':  cropper.zoom(0.1); break;
                case 'zoomout': cropper.zoom(-0.1); break;
                case 'rotl':    cropper.rotate(-90); break;
                case 'rotr':    cropper.rotate(90); break;
                case 'reset':   cropper.reset(); break;
            }
        });
    });

    // On submit, freeze the cropped canvas into the hidden field.
    form.addEventListener('submit', function (e) {
        if (cropper) {
            var canvas = cropper.getCroppedCanvas({
                width: 400, height: 400,
                imageSmoothingEnabled: true, imageSmoothingQuality: 'high'
            });
            if (canvas) {
                hidden.value = canvas.toDataURL('image/jpeg', 0.9);
                // With a crop present we don't also send the raw file.
                fileInput.removeAttribute('name');
            }
        }
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    });
})();
</script>

<?php include('footer.php'); ?>
<script src="../../main.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
