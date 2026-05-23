-- Migration: track which Public-Library exam a teacher republished into their school.
--
-- When a teacher clicks "Republish to my school" on a Public-Library exam,
-- clone_exam.php inserts a brand-new exams row owned by them. We store the
-- source exam_id here so the dashboard can render a permanent
-- "📝 Adapted from [Original Teacher]" credit chip on the cloned row.
--
-- Safe to run more than once: uses IF NOT EXISTS so re-running is a no-op.
ALTER TABLE `exams`
  ADD COLUMN IF NOT EXISTS `cloned_from_exam_id` INT NULL DEFAULT NULL
  COMMENT 'Source exam_id when this exam was republished from the Public Library.';
