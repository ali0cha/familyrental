-- ─────────────────────────────────────────────────────────────────────────────
-- Family Villa — Schéma de base de données
-- Compatible MySQL 5.7+ / MariaDB 10.3+
-- ─────────────────────────────────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS `your_database_name`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `your_database_name`;

-- ── Utilisateurs ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `username`       VARCHAR(80)     NOT NULL UNIQUE,
    `email`          VARCHAR(255)    NOT NULL UNIQUE,
    `password`       VARCHAR(255)    NOT NULL,           -- bcrypt via password_hash()
    `role`           ENUM('member','admin') NOT NULL DEFAULT 'member',
    `is_active`      TINYINT(1)      NOT NULL DEFAULT 0, -- 0 = en attente, 1 = validé
    `reset_token`    VARCHAR(64)     DEFAULT NULL,
    `reset_expires`  DATETIME        DEFAULT NULL,
    `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_role_active` (`role`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Réservations ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    NOT NULL,
    `start_date` DATE            NOT NULL,
    `end_date`   DATE            NOT NULL,
    `status`     ENUM('en attente','approuvée','refusée') NOT NULL DEFAULT 'en attente',
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user`   (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_dates`  (`start_date`, `end_date`),
    CONSTRAINT `fk_booking_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- Créer le premier compte administrateur
-- Remplacez les valeurs ci-dessous, puis exécutez cette requête UNE seule fois.
-- Le mot de passe doit être haché via PHP : echo password_hash('votre_mdp', PASSWORD_DEFAULT);
-- ─────────────────────────────────────────────────────────────────────────────
-- INSERT INTO `users` (`username`, `email`, `password`, `role`, `is_active`)
-- VALUES ('admin', 'admin@example.com', '$2y$...hash...', 'admin', 1);
