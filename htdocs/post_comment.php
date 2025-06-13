<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// ログイン確認
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

// POSTデータ確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['thread_id']) || !isset($_POST['content'])) {
    header("Location: home.php");
    exit;
}

// スレッド存在確認
$stmt = $pdo->prepare("SELECT 1 FROM threads WHERE thread_id = ?");
$stmt->execute([$_POST['thread_id']]);
if (!$stmt->fetch()) {
    header("Location: home.php");
    exit;
}

// コメント投稿
$stmt = $pdo->prepare("INSERT INTO comments (thread_id, student_id, content) VALUES (?, ?, ?)");
$stmt->execute([
    $_POST['thread_id'],
    $_SESSION['student_id'],
    trim($_POST['content'])
]);

header("Location: thread.php?id=" . $_POST['thread_id']);
exit;
