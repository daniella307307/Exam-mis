<?php
/**
 * Developer curriculum manager — Week editor + Bunny upload.
 * URL: curriculum_week.php?CERT=<id>&TERM=<1-3>&MONTH=<1-12>&WEEK=<n>
 *
 * Two-panel layout:
 *   - left:  PDF + video preview (when saved)
 *   - right: editor — title, notes, PDF Bunny URL, Video Bunny URL, two
 *            file pickers that upload straight to Bunny CDN and auto-fill
 *            the URL fields (no copy/paste needed).
 *
 * Save flow: form posts to save_curriculum_week.php (Developer-gated upsert).
 * Upload flow: file input → curriculum_upload_bunny.php → URL → fills field
 * → user clicks Save. Two steps deliberately, so the Developer can preview
 * the chosen file before committing it to the row.
 */
include('header.php');
require_once(__DIR__ . '/../curriculum_helpers.php');

curriculum_ensure_table($conn);

$CERT  = isset($_GET['CERT'])  ? (int)$_GET['CERT']  : 0;
$TERM  = isset($_GET['TERM'])  ? (int)$_GET['TERM']  : 0;
$MONTH = isset($_GET['MONTH']) ? (int)$_GET['MONTH'] : 0;
$WEEK  = isset($_GET['WEEK'])  ? (int)$_GET['WEEK']  : 0;

if ($CERT <= 0 || !curriculum_valid_slot($TERM, $MONTH, $WEEK)) {
    echo '<p class="p-4">Bad slot. <a href="Curriculum.php" class="text-blue-600 underline">Back</a>.</p>';
    include('footer.php'); exit;
}

$cstmt = $conn->prepare("SELECT certification_id, certification_name FROM certifications WHERE certification_id = ? LIMIT 1");
$cstmt->bind_param('i', $CERT);
$cstmt->execute();
$cert = $cstmt->get_result()->fetch_assoc();
$cstmt->close();
if (!$cert) { echo '<p class="p-4">Certification not found.</p>'; include('footer.php'); exit; }

$row = null;
$rstmt = $conn->prepare(
    "SELECT title, notes, bunny_pdf_url, bunny_video_url, updated_at
       FROM curriculum_weeks
      WHERE certification_id = ? AND term_number = ? AND month_number = ? AND week_number = ?
      LIMIT 1"
);
$rstmt->bind_param('iiii', $CERT, $TERM, $MONTH, $WEEK);
$rstmt->execute();
$row = $rstmt->get_result()->fetch_assoc();
$rstmt->close();

$terms     = curriculum_terms();
$names     = curriculum_month_names();
$title     = (string)($row['title']           ?? '');
$notes     = (string)($row['notes']           ?? '');
$pdf_url   = (string)($row['bunny_pdf_url']   ?? '');
$video_url = (string)($row['bunny_video_url'] ?? '');
$updated   = $row['updated_at'] ?? null;
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">
        <div class="flex items-center gap-3 mb-3">
            <a href="curriculum_weeks.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>&MONTH=<?= (int)$MONTH ?>"
               class="back-btn">← Back to weeks in <?= htmlspecialchars($names[$MONTH]) ?></a>
        </div>
        <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide font-semibold">
            <a href="Curriculum.php" class="hover:text-purple-600">Curriculum</a>
            <span class="mx-1">›</span>
            <a href="curriculum_terms.php?CERT=<?= (int)$CERT ?>" class="hover:text-purple-600"><?= htmlspecialchars($cert['certification_name']) ?></a>
            <span class="mx-1">›</span>
            <a href="curriculum_months.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>" class="hover:text-purple-600"><?= htmlspecialchars($terms[$TERM]['label']) ?></a>
            <span class="mx-1">›</span>
            <a href="curriculum_weeks.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>&MONTH=<?= (int)$MONTH ?>" class="hover:text-purple-600"><?= htmlspecialchars($names[$MONTH]) ?></a>
            <span class="mx-1">›</span>
            <span class="text-gray-800">Week <?= (int)$WEEK ?></span>
        </div>

        <div class="flex justify-between items-end mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Week <?= (int)$WEEK ?> <span class="text-gray-400">·</span> <?= htmlspecialchars($names[$MONTH]) ?>
                </h1>
                <p class="text-sm text-gray-500">
                    <?= htmlspecialchars($cert['certification_name']) ?> · <?= htmlspecialchars($terms[$TERM]['label']) ?> (<?= htmlspecialchars($terms[$TERM]['range']) ?>)
                    <?php if ($updated): ?>
                        <span class="ml-2">· last updated <?= htmlspecialchars($updated) ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            <!-- LEFT: Live preview -->
            <div class="lg:col-span-3 space-y-4">
                <div class="card">
                    <div class="card-h">📄 PDF preview</div>
                    <div id="pdf-preview-wrap">
                        <?php if ($pdf_url !== ''): ?>
                            <iframe src="<?= htmlspecialchars($pdf_url) ?>#toolbar=0" class="pdf-frame"></iframe>
                            <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="text-xs text-purple-600 underline mt-2 inline-block">Open in new tab ↗</a>
                        <?php else: ?>
                            <div class="empty">No PDF yet. Upload one on the right or paste a Bunny URL.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-h">🎬 Video preview</div>
                    <div id="video-preview-wrap">
                        <?php if ($video_url !== ''): ?>
                            <video controls preload="metadata" class="video-frame">
                                <source src="<?= htmlspecialchars($video_url) ?>">
                                Your browser cannot play this video.
                            </video>
                            <a href="<?= htmlspecialchars($video_url) ?>" target="_blank" class="text-xs text-purple-600 underline mt-2 inline-block">Open in new tab ↗</a>
                        <?php else: ?>
                            <div class="empty">No video yet. Upload one on the right or paste a Bunny URL.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Editor + uploaders -->
            <aside class="lg:col-span-2">
                <form id="cw-form" class="card sticky-pane">
                    <div class="card-h">✏️ Manage this week</div>

                    <label class="form-lbl">Week title</label>
                    <input type="text" name="title" maxlength="255" id="f-title"
                           value="<?= htmlspecialchars($title) ?>"
                           placeholder="e.g. Counting 1–10"
                           class="form-in">

                    <!-- PDF block -->
                    <label class="form-lbl">📄 PDF</label>
                    <input type="url" name="bunny_pdf_url" id="f-pdf"
                           value="<?= htmlspecialchars($pdf_url) ?>"
                           placeholder="https://bluelakes1988.b-cdn.net/curriculum/…"
                           class="form-in">
                    <div class="uploader" data-kind="pdf">
                        <input type="file" accept="application/pdf,.pdf" id="pdf-file" hidden>
                        <button type="button" class="upload-btn" data-target="pdf-file">⬆️ Upload PDF to Bunny</button>
                        <span class="upload-status" id="pdf-status"></span>
                    </div>

                    <!-- Video block -->
                    <label class="form-lbl">🎬 Video</label>
                    <input type="url" name="bunny_video_url" id="f-video"
                           value="<?= htmlspecialchars($video_url) ?>"
                           placeholder="https://bluelakes1988.b-cdn.net/curriculum/…"
                           class="form-in">
                    <div class="uploader" data-kind="video">
                        <input type="file" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.m4v" id="video-file" hidden>
                        <button type="button" class="upload-btn" data-target="video-file">⬆️ Upload Video to Bunny</button>
                        <span class="upload-status" id="video-status"></span>
                    </div>

                    <label class="form-lbl">Notes for the teacher (optional)</label>
                    <textarea name="notes" rows="4"
                              placeholder="Lesson plan, talking points, prep…"
                              class="form-in"><?= htmlspecialchars($notes) ?></textarea>

                    <button type="submit" class="save-btn">💾 Save week</button>
                    <div id="cw-status" class="text-xs mt-2 min-h-[1em]"></div>
                </form>
            </aside>
        </div>
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
    .back-btn:hover { transform:translateX(-2px); border-color:#7c3aed; color:#7c3aed; box-shadow:0 4px 10px rgba(0,0,0,.06); }
    .card {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:18px 20px;
        box-shadow:0 4px 12px rgba(0,0,0,.04);
    }
    .card-h {
        font-size:12px; font-weight:800; letter-spacing:1.5px; text-transform:uppercase;
        color:#7c3aed; margin-bottom:12px;
    }
    .pdf-frame   { width:100%; height:560px; border:1px solid #e5e7eb; border-radius:8px; }
    .video-frame { width:100%; max-height:420px; border-radius:8px; background:#000; }
    .empty {
        padding:30px; text-align:center; color:#9ca3af;
        background:#f9fafb; border:1px dashed #e5e7eb; border-radius:8px;
        font-size:13px;
    }
    .sticky-pane { position:sticky; top:14px; }
    .form-lbl { display:block; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:#6b7280; margin:14px 0 6px; }
    .form-in {
        width:100%; padding:10px 12px;
        border:1.5px solid #e5e7eb; border-radius:8px;
        font-size:14px; color:#111827; background:#fff;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    .form-in:focus { outline:none; border-color:#7c3aed; box-shadow:0 0 0 4px rgba(124,58,237,.12); }
    .uploader {
        display:flex; gap:10px; align-items:center; flex-wrap:wrap;
        margin-top:6px;
    }
    .upload-btn {
        padding:8px 14px; border-radius:8px; border:1.5px solid #c4b5fd;
        background:#f5f3ff; color:#5b21b6; font-weight:700; font-size:12px;
        cursor:pointer; transition:transform .12s ease, background .12s ease;
    }
    .upload-btn:hover { transform:translateY(-1px); background:#ede9fe; }
    .upload-btn:disabled { opacity:.6; cursor:wait; }
    .upload-status { font-size:11px; font-weight:700; color:#6b7280; }
    .save-btn {
        margin-top:18px; width:100%;
        padding:12px 16px; border:none; border-radius:8px; cursor:pointer;
        background:linear-gradient(135deg,#7c3aed,#a855f7); color:#fff;
        font-weight:800; font-size:14px; letter-spacing:.3px;
        box-shadow:0 8px 22px rgba(124,58,237,.35);
        transition:transform .15s ease;
    }
    .save-btn:hover    { transform:translateY(-1px); }
    .save-btn:disabled { opacity:.6; cursor:wait; }
</style>

<script>
const CTX = {
    cert:  <?= (int)$CERT ?>,
    term:  <?= (int)$TERM ?>,
    month: <?= (int)$MONTH ?>,
    week:  <?= (int)$WEEK ?>,
};

// "Upload to Bunny" buttons trigger a hidden <input type=file>, push the
// file through curriculum_upload_bunny.php, then drop the resulting CDN
// URL into the visible URL field + refresh the preview pane on the left.
document.querySelectorAll('.upload-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const targetId = btn.dataset.target;
        document.getElementById(targetId).click();
    });
});

function bindUploader(kind, fileInputId, urlInputId, statusId, previewWrapId) {
    const fileEl = document.getElementById(fileInputId);
    const urlEl  = document.getElementById(urlInputId);
    const stat   = document.getElementById(statusId);
    const preview = document.getElementById(previewWrapId);

    fileEl.addEventListener('change', async () => {
        if (!fileEl.files.length) return;
        const file = fileEl.files[0];
        const fd = new FormData();
        fd.append('kind',  kind);
        fd.append('cert',  CTX.cert);
        fd.append('term',  CTX.term);
        fd.append('month', CTX.month);
        fd.append('week',  CTX.week);
        fd.append('file',  file);

        // Disable both buttons so the dev can't fire a second upload mid-stream.
        const btns = document.querySelectorAll('.upload-btn');
        btns.forEach((b) => b.disabled = true);

        // Naive progress: we don't get true progress out of fetch() for
        // file uploads without XHR, so we just show "uploading…". For big
        // videos that's fine — the dev can hear from the success message.
        stat.style.color = '#6b7280';
        stat.textContent = `⏳ Uploading ${file.name} (${(file.size / 1024 / 1024).toFixed(1)}MB)…`;

        try {
            const r = await fetch('curriculum_upload_bunny.php', { method: 'POST', body: fd });
            const j = await r.json();
            if (!j.success) throw new Error(j.error || 'upload failed');

            urlEl.value = j.url;
            stat.style.color = '#059669';
            stat.textContent = '✅ Uploaded — remember to click Save week.';

            // Refresh the matching preview pane inline (no full reload yet).
            if (kind === 'pdf') {
                preview.innerHTML =
                    `<iframe src="${j.url}#toolbar=0" class="pdf-frame"></iframe>` +
                    `<a href="${j.url}" target="_blank" class="text-xs text-purple-600 underline mt-2 inline-block">Open in new tab ↗</a>`;
            } else {
                preview.innerHTML =
                    `<video controls preload="metadata" class="video-frame">` +
                    `<source src="${j.url}"></video>` +
                    `<a href="${j.url}" target="_blank" class="text-xs text-purple-600 underline mt-2 inline-block">Open in new tab ↗</a>`;
            }
        } catch (err) {
            stat.style.color = '#dc2626';
            stat.textContent = '❌ ' + err.message;
        } finally {
            btns.forEach((b) => b.disabled = false);
            fileEl.value = ''; // allow re-upload of same file
        }
    });
}

bindUploader('pdf',   'pdf-file',   'f-pdf',   'pdf-status',   'pdf-preview-wrap');
bindUploader('video', 'video-file', 'f-video', 'video-status', 'video-preview-wrap');

// Save the row (upserts).
document.getElementById('cw-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const btn  = form.querySelector('.save-btn');
    const stat = document.getElementById('cw-status');
    const fd   = new FormData(form);
    fd.append('cert',  CTX.cert);
    fd.append('term',  CTX.term);
    fd.append('month', CTX.month);
    fd.append('week',  CTX.week);

    btn.disabled = true;
    stat.style.color = '#6b7280';
    stat.textContent = 'Saving…';

    try {
        const r = await fetch('save_curriculum_week.php', { method: 'POST', body: fd });
        const j = await r.json();
        if (!j.success) throw new Error(j.error || 'save failed');
        stat.style.color = '#059669';
        stat.textContent = '✅ Saved.';
    } catch (err) {
        stat.style.color = '#dc2626';
        stat.textContent = '❌ ' + err.message;
    } finally {
        btn.disabled = false;
    }
});
</script>

<?php include('footer.php'); ?>
