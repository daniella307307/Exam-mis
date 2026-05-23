<?php
/**
 * Shared term/month math for the Cambridge-style curriculum browser.
 *
 * Layout (3-term calendar year, 4 months per term):
 *   Term 1 — Jan / Feb / Mar / Apr
 *   Term 2 — May / Jun / Jul / Aug
 *   Term 3 — Sep / Oct / Nov / Dec
 *
 * Per the user, the trailing month of each term (Apr, Aug, Dec) is treated
 * as a "projects month" — taught lighter, more PBL-style. The UI flags it
 * but doesn't gate access.
 *
 * Also: idempotently ensures the curriculum_weeks table exists on first hit,
 * so the new pages render without the SQL migration having to be applied by
 * hand.
 */

if (!function_exists('curriculum_ensure_table')) {
    function curriculum_ensure_table(mysqli $conn): void {
        static $checked = false;
        if ($checked) return;
        $check = $conn->query("SHOW TABLES LIKE 'curriculum_weeks'");
        if ($check && $check->num_rows === 0) {
            @ $conn->query(
                "CREATE TABLE IF NOT EXISTS `curriculum_weeks` (
                    `cw_id`            INT          NOT NULL AUTO_INCREMENT,
                    `certification_id` INT          NOT NULL,
                    `term_number`      TINYINT      NOT NULL,
                    `month_number`     TINYINT      NOT NULL,
                    `week_number`      TINYINT      NOT NULL,
                    `title`            VARCHAR(255) NULL DEFAULT NULL,
                    `notes`            TEXT         NULL DEFAULT NULL,
                    `bunny_pdf_url`    TEXT         NULL DEFAULT NULL,
                    `bunny_video_url`  TEXT         NULL DEFAULT NULL,
                    `updated_by`       INT          NULL DEFAULT NULL,
                    `created_at`       TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`       TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`cw_id`),
                    UNIQUE KEY `uniq_cw_slot` (`certification_id`, `term_number`, `month_number`, `week_number`),
                    KEY `idx_cw_cert_term`   (`certification_id`, `term_number`),
                    KEY `idx_cw_cert_month`  (`certification_id`, `month_number`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
        if ($check) { $check->close(); }
        $checked = true;
    }
}

if (!function_exists('curriculum_terms')) {
    /**
     * The three Cambridge-style terms with month ranges + the months they
     * contain. Kept as data (not constants) so future tweaks stay in one file.
     */
    function curriculum_terms(): array {
        return [
            1 => ['label' => 'Term 1', 'range' => 'Jan – Apr', 'months' => [1, 2, 3, 4]],
            2 => ['label' => 'Term 2', 'range' => 'May – Aug', 'months' => [5, 6, 7, 8]],
            3 => ['label' => 'Term 3', 'range' => 'Sep – Dec', 'months' => [9, 10, 11, 12]],
        ];
    }
}

if (!function_exists('curriculum_month_names')) {
    function curriculum_month_names(): array {
        return [
            1=>'January', 2=>'February', 3=>'March',     4=>'April',
            5=>'May',     6=>'June',     7=>'July',      8=>'August',
            9=>'September',10=>'October',11=>'November',12=>'December',
        ];
    }
}

if (!function_exists('curriculum_term_for_month')) {
    function curriculum_term_for_month(int $month): int {
        if ($month >= 1 && $month <= 4)  return 1;
        if ($month >= 5 && $month <= 8)  return 2;
        if ($month >= 9 && $month <= 12) return 3;
        return 0;
    }
}

if (!function_exists('curriculum_is_projects_month')) {
    /** April, August and December are the "projects months" per the user. */
    function curriculum_is_projects_month(int $month): bool {
        return in_array($month, [4, 8, 12], true);
    }
}

if (!function_exists('curriculum_default_week_count')) {
    /** Standard 4 weeks per month. Final month of a term gets 5 to fit the
     *  exam/wrap-up week — easy to change later. */
    function curriculum_default_week_count(int $month): int {
        return curriculum_is_projects_month($month) ? 5 : 4;
    }
}

if (!function_exists('curriculum_valid_slot')) {
    /** Defensive: refuse term/month/week combos that don't line up. */
    function curriculum_valid_slot(int $term, int $month, int $week): bool {
        $terms = curriculum_terms();
        if (!isset($terms[$term])) return false;
        if (!in_array($month, $terms[$term]['months'], true)) return false;
        if ($week < 1 || $week > 6) return false;
        return true;
    }
}

if (!function_exists('curriculum_safe_path_segment')) {
    /**
     * Sanitize a free-form name (e.g. "Nursery I" or "Cambridge Year 1") into
     * a Bunny-storage-safe folder segment. Strips anything outside
     * A-Z/a-z/0-9/._- and collapses runs of underscores so the path stays tidy
     * even with messy input.
     */
    function curriculum_safe_path_segment(string $name): string {
        $name = trim($name);
        if ($name === '') return 'untitled';
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
        $name = preg_replace('/_+/', '_', (string)$name);
        $name = trim((string)$name, '_-.');
        return $name === '' ? 'untitled' : $name;
    }
}

if (!function_exists('curriculum_video_mime_for_url')) {
    /**
     * Pick a sensible MIME for a <source type="…"> attribute given a Bunny
     * URL. Without this, some browsers (Safari especially) refuse to load
     * the media because they don't bother sniffing — they need the type
     * hint up front. That was the "click play and nothing happens" bug.
     */
    function curriculum_video_mime_for_url(string $url): string {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map  = [
            'mp4'  => 'video/mp4',
            'm4v'  => 'video/mp4',
            'webm' => 'video/webm',
            'ogg'  => 'video/ogg',
            'ogv'  => 'video/ogg',
            'mov'  => 'video/quicktime',
        ];
        return $map[$ext] ?? 'video/mp4';
    }
}
