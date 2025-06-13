-- (A1) 既存のDB・ユーザー・テーブルの削除（安全な初期化）

-- 既存のデータベースを削除（完全初期化が目的の場合）
DROP DATABASE IF EXISTS mydb;

-- ユーザーを削除（存在する場合）
DROP USER IF EXISTS 'testuser'@'localhost';

-- (A2) データベースの再作成
CREATE DATABASE mydb 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

-- (A3) ユーザー再作成と権限付与（安全な設定）
CREATE USER 'testuser'@'localhost' IDENTIFIED BY 'pass';
GRANT ALL PRIVILEGES ON mydb.* TO 'testuser'@'localhost';

-- (A4) 使用するデータベースを選択
USE mydb;

-- 念のため：既存テーブル削除（順序注意：依存の少ない順）
DROP TABLE IF EXISTS uploaded_folders;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS threads;
DROP TABLE IF EXISTS users;

-- ユーザーテーブル作成
CREATE TABLE users (
    student_id CHAR(7) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE
);

-- スレッドテーブル
CREATE TABLE threads (
    thread_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    created_by CHAR(7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(student_id)
);

-- コメントテーブル
CREATE TABLE comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    student_id CHAR(7) NOT NULL,
    content TEXT NOT NULL,
    file_path TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES threads(thread_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(student_id)
);

-- アップロードフォルダテーブル
CREATE TABLE uploaded_folders (
    folder_id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    folder_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE
);

-- 管理者アカウントを1件登録
INSERT INTO users (student_id, name, password_hash, is_admin)
VALUES (
  '9877389',
  '【管理者】研究室長',
  '$2y$10$OYtBZzRbEO3UmJZzV7IKnOYzfdKRz7lNTDyz3Zrbi5UmmvG2hL9WC',
  TRUE
);