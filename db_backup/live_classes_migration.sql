-- Live online classes posted by teachers.
-- Each row is a single Google Meet (or any conferencing URL) shared
-- with other teachers. Teachers can join, the host can delete their
-- own session, and the displayed status is derived from start/end
-- timestamps so we never have to back-fill a "live"/"ended" column.

CREATE TABLE IF NOT EXISTS `live_classes` (
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `host_user_id`   INT(11) NOT NULL,
    `title`          VARCHAR(180) NOT NULL,
    `description`    TEXT NULL,
    `meet_link`      VARCHAR(500) NOT NULL,
    `scheduled_at`   DATETIME NOT NULL,
    `duration_min`   SMALLINT(5) UNSIGNED NOT NULL DEFAULT 60,
    `audience`       VARCHAR(80) NOT NULL DEFAULT 'teachers',
    `is_cancelled`   TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_scheduled_at` (`scheduled_at`),
    KEY `idx_host`         (`host_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
