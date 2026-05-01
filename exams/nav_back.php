<?php
/**
 * Reusable Back Navigation Component
 * Usage: <?php $back_to = 'exams_dashboard.php'; $back_label = 'Dashboard'; include('nav_back.php'); ?>
 * 
 * Defaults to exams_dashboard.php if not set
 */
$back_to = $back_to ?? 'exams_dashboard.php';
$back_label = $back_label ?? 'Dashboard';
?>
<div style="position:fixed; top:16px; left:16px; z-index:9999; display:flex; gap:8px;">
    <a href="<?= htmlspecialchars($back_to) ?>" 
       style="display:inline-flex; align-items:center; gap:8px; padding:10px 16px; 
              background:rgba(255,255,255,0.95); color:#374151; text-decoration:none; 
              border-radius:10px; font-weight:700; font-size:14px; 
              box-shadow:0 4px 12px rgba(0,0,0,0.15); border:1px solid #e5e7eb;
              transition:all 0.2s;"
       onmouseover="this.style.background='#667eea'; this.style.color='white';"
       onmouseout="this.style.background='rgba(255,255,255,0.95)'; this.style.color='#374151';">
        ← Back to <?= htmlspecialchars($back_label) ?>
    </a>
    <button onclick="history.back()" 
            style="padding:10px 14px; background:rgba(255,255,255,0.95); color:#374151; 
                   border:1px solid #e5e7eb; border-radius:10px; font-weight:700; 
                   font-size:14px; cursor:pointer; box-shadow:0 4px 12px rgba(0,0,0,0.15);
                   transition:all 0.2s;"
            onmouseover="this.style.background='#667eea'; this.style.color='white';"
            onmouseout="this.style.background='rgba(255,255,255,0.95)'; this.style.color='#374151';"
            title="Go back to previous page">
        ↶ Previous
    </button>
</div>