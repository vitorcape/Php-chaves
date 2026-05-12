-- ============================================================
-- CRUD App — Setup do Banco de Dados
-- Execute no phpMyAdmin ou via MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS crud_app
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE crud_app;

-- ----------------------------------------
-- Tabela: users
-- ----------------------------------------
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('admin', 'editor', 'visitor') NOT NULL DEFAULT 'visitor',
    job        VARCHAR(100)  DEFAULT NULL,
    active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_role   (role),
    INDEX idx_active (active)
);

-- ----------------------------------------
-- Tabela: posts
-- ----------------------------------------
CREATE TABLE posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    title      VARCHAR(200) NOT NULL,
    slug       VARCHAR(220) NOT NULL,
    content    TEXT,
    status     ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_slug   (slug),
    INDEX      idx_status  (status),
    INDEX      idx_user_id (user_id),

    CONSTRAINT fk_posts_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
