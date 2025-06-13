<?php
# HTMLでのエスケープ処理をする関数（データベースとは無関係だが，ついでにここで定義しておく．）
// この関数定義はfunctions.phpに移動しました

// SQLで作成したデータベース設定を使用
$dbServer = isset($_ENV['MYSQL_SERVER'])    ? $_ENV['MYSQL_SERVER']      : '127.0.0.1';
$dbUser = isset($_SERVER['MYSQL_USER'])     ? $_SERVER['MYSQL_USER']     : 'testuser';
$dbPass = isset($_SERVER['MYSQL_PASSWORD']) ? $_SERVER['MYSQL_PASSWORD'] : 'pass';
$dbName = isset($_SERVER['MYSQL_DB'])       ? $_SERVER['MYSQL_DB']       : 'mydb';

$dsn = "mysql:host={$dbServer};dbname={$dbName};charset=utf8";

// 定数設定（SQLの設定に合わせる）
define('DB_HOST', $dbServer);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_NAME', $dbName);
define('MAX_FILE_SIZE', 3221225472); // 3GB
define('UPLOAD_DIR', 'uploads/');

ini_set('memory_limit', '3G');
set_time_limit(300);

// パスワードハッシュ用定数
define('PASSWORD_ALGO', PASSWORD_DEFAULT);
define('PASSWORD_OPTIONS', ['cost' => 12]);

try {
  $pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]
  );
} catch (PDOException $e) {
  die("Can't connect to the database: " . $e->getMessage());
}

session_start();
