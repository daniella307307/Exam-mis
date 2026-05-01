-- Password reset tokens.
-- Each row is a one-shot, time-limited reset request. The token stored
-- here is a SHA-256 hash of the random value emailed to the user, so
-- a database leak does not let an attacker reset accounts.

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_kind`    ENUM('user','student') NOT NULL,
    `user_ref_id`  INT(11) NOT NULL,
    `email`        VARCHAR(190) NOT NULL,
    `token_hash`   CHAR(64) NOT NULL,
    `expires_at`   DATETIME NOT NULL,
    `used_at`      DATETIME NULL DEFAULT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `requester_ip` VARCHAR(45) NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_token_hash` (`token_hash`),
    KEY `idx_user`    (`user_kind`,`user_ref_id`),
    KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
