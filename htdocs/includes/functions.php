<?php
// 既存のcalculate_password関数を削除
// 新規関数追加
function verify_password($password, $hash)
{
  return password_verify($password, $hash);
}

function is_admin()
{
  if (!isset($_SESSION['student_id'])) return false;
  global $pdo;
  $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE student_id = ?");
  $stmt->execute([$_SESSION['student_id']]);
  $user = $stmt->fetch();
  return $user && $user['is_admin'];
}

// 型ヒントを追加して重複を防ぐ
function h(string|array $var): string|array
{
  if (is_array($var)) {
    return array_map('h', $var);
  } else {
    return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
  }
}

function format_size($bytes)
{
  $units = ['B', 'KB', 'MB', 'GB'];
  $bytes = max($bytes, 0);
  $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
  $pow = min($pow, count($units) - 1);
  $bytes /= (1 << (10 * $pow));
  return round($bytes, 2) . ' ' . $units[$pow];
}

function handle_zip_upload($file, $comment_id)
{
  global $pdo;
  $zip = new ZipArchive;
  if ($zip->open($file['tmp_name']) === TRUE) {
    $folder_name = uniqid() . '_' . preg_replace('/\.[^.]+$/', '', $file['name']);
    $target_dir = UPLOAD_DIR . $folder_name . '/';

    mkdir($target_dir, 0755, true);
    $zip->extractTo($target_dir);
    $zip->close();

    $stmt = $pdo->prepare("INSERT INTO uploaded_folders (comment_id, folder_path) VALUES (?, ?)");
    $stmt->execute([$comment_id, $target_dir]);
    return $target_dir;
  }
  return false;
}
