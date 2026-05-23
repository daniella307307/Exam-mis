-- Migration: Cambridge-style curriculum content per (certification, term, month, week).
--
-- Lets teachers drill: Grade Level → Term (1-3) → Month (1-12) → Week (1-N)
-- and attach Bunny CDN URLs for the PDF + video that students will watch.
--
-- One row per (certification_id, term, month, week) — UNIQUE so the upsert in
-- save_curriculum_week.php has something to match against. The dashboard PHP
-- also idempotently creates this table if it's missing, so running this SQL
-- manually is optional.
CREATE TABLE IF NOT EXISTS `curriculum_weeks` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
