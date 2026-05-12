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

CREATE TABLE iot_devices (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    device_id   VARCHAR(64)  NOT NULL UNIQUE,          -- ex: "esp32-sala"
    button      TINYINT(1)   NOT NULL DEFAULT 0,       -- estado do botão físico
    led         TINYINT(1)   NOT NULL DEFAULT 0,       -- estado atual do LED
    last_seen   DATETIME     DEFAULT NULL,             -- último contato
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE iot_commands (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    device_id   VARCHAR(64)  NOT NULL,
    command     VARCHAR(64)  NOT NULL,                 -- ex: "led"
    value       TINYINT      NOT NULL DEFAULT 0,       -- 0 ou 1
    executed    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_device_pending (device_id, executed)
);
 
-- Dispositivo padrão para testes
INSERT IGNORE INTO iot_devices (device_id) VALUES ('esp32-01');
