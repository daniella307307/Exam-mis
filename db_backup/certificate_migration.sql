-- ============================================================
-- CERTIFICATE MIGRATION
-- Run this once on your remote database to enable certificates
-- ============================================================

-- 1. Add passing_score column to exams (default 50%)
ALTER TABLE `exams`
  ADD COLUMN IF NOT EXISTS `passing_score` INT NOT NULL DEFAULT 50
  COMMENT 'Minimum percentage to pass and earn certificate';

-- 2. Add exam_certification column to exams (links to certifications table)
ALTER TABLE `exams`
  ADD COLUMN IF NOT EXISTS `exam_certification` INT NULL DEFAULT NULL
  COMMENT 'FK to certifications.certification_id';

-- 3. Create student_certificates table
CREATE TABLE IF NOT EXISTS `student_certificates` (
  `cert_id`            INT AUTO_INCREMENT PRIMARY KEY,
  `player_id`          INT NOT NULL,
  `exam_id`            INT NOT NULL,
  `player_name`        VARCHAR(150) NOT NULL,
  `exam_title`         VARCHAR(250) NOT NULL,
  `certification_id`   INT NULL DEFAULT NULL,
  `certification_name` VARCHAR(200) NULL DEFAULT NULL,
  `score`              INT NOT NULL DEFAULT 0,
  `total_marks`        INT NOT NULL DEFAULT 0,
  `percentage`         INT NOT NULL DEFAULT 0,
  `issued_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `cert_code`          VARCHAR(60) NOT NULL,
  UNIQUE KEY `unique_player_exam` (`player_id`, `exam_id`),
  UNIQUE KEY `unique_cert_code` (`cert_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
