-- (A2) データベース作成（既存の場合はスキップ）
CREATE DATABASE IF NOT EXISTS mydb 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

-- (A3) ユーザー作成（安全な方法で）
DROP USER IF EXISTS 'testuser'@'localhost'; -- 既存ユーザー削除
CREATE USER 'testuser'@'localhost' IDENTIFIED BY 'pass';
GRANT ALL PRIVILEGES ON mydb.* TO 'testuser'@'localhost';

-- (A4) mydbを使うことを宣言する
USE mydb;

-- ユーザーテーブル（先に基本構造を作成）
CREATE TABLE users (
    student_id CHAR(7) PRIMARY KEY
);

-- 必要な列を追加（順序通りに）
ALTER TABLE users 
  ADD COLUMN name VARCHAR(100) NOT NULL,
  ADD COLUMN password_hash VARCHAR(255) NOT NULL,
  ADD COLUMN is_admin BOOLEAN NOT NULL DEFAULT FALSE;

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

-- 管理者アカウント登録（重複しないように1回のみ）
INSERT INTO users (student_id, name, password_hash, is_admin)
VALUES (
  '9877389',
  '【管理者】研究室長',
  '$2y$10$OYtBZzRbEO3UmJZzV7IKnOYzfdKRz7lNTDyz3Zrbi5UmmvG2hL9WC', 
  TRUE
);