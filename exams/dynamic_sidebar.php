<?php
/**
 * Slim sidebar for exam pages.
 *
 * Per the user's request, the long list of LMS links (Lab Equipments,
 * Levels/Classes, Students, Profile, Logout, etc.) is intentionally
 * removed on the exam side — those belong on the main LMS sidebar.
 * Here we only render a "Back to LMS Home" button so a teacher can
 * always exit the exam tooling cleanly.
 */
if (!isset($conn) || !($conn instanceof mysqli)) {
    @include __DIR__ . '/../db.php';
}

if ($_SERVER['SERVER_NAME'] === 'localhost' && is_dir('/var/www/html/Exam-mis')) {
    $base_url = '/Exam-mis';
} elseif ($_SERVER['SERVER_NAME'] === 'localhost') {
    $base_url = '/_bluelackesadigital.com/public_html';
} else {
    $base_url = '';
}
?>
<aside id="sidebar" class="bg-side-nav border-r border-side-nav hidden md:block lg:block min-h-screen"
       style="background:rgba(15,15,40,.55) !important;border-right:1px solid rgba(168,85,247,.3) !important;padding:24px 14px;width:240px">

    <a href="<?php echo $base_url; ?>/Auth/SF/index.php"
       style="display:flex;align-items:center;gap:10px;
              padding:12px 16px;
              background:linear-gradient(135deg,#7c3aed,#a855f7);
              color:#fff;font-weight:800;font-size:14px;
              border-radius:10px;text-decoration:none;
              box-shadow:0 8px 24px rgba(124,58,237,.4);
              transition:transform .15s">
        <i class="fas fa-home"></i>
        <span>Back to LMS Home</span>
    </a>

    <a href="<?php echo $base_url; ?>/exams/live_classes.php"
       style="display:flex;align-items:center;gap:10px;
              margin-top:12px;padding:12px 16px;
              background:rgba(255,255,255,.06);
              color:#f1f5f9;font-weight:700;font-size:13px;
              border-radius:10px;text-decoration:none;
              border:1px solid rgba(168,85,247,.25)">
        <i class="fas fa-video"></i>
        <span>Join Online Class</span>
    </a>
</aside>
<?php // Don't close $conn — the host page may still need it. ?>
