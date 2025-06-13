<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// ログイン確認
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['thread_id'])) {
        // スレッド削除
        $stmt = $pdo->prepare("SELECT created_by FROM threads WHERE thread_id = ?");
        $stmt->execute([$_POST['thread_id']]);
        $thread = $stmt->fetch();

        if ($thread && ($_SESSION['student_id'] === $thread['created_by'] || $_SESSION['student_id'] === '9877389')) {
            $pdo->prepare("DELETE FROM threads WHERE thread_id = ?")->execute([$_POST['thread_id']]);
            header("Location: home.php");
            exit;
        }
    } elseif (isset($_POST['comment_id'])) {
        // コメント削除
        $stmt = $pdo->prepare("SELECT thread_id, student_id FROM comments WHERE comment_id = ?");
        $stmt->execute([$_POST['comment_id']]);
        $comment = $stmt->fetch();

        if ($comment && ($_SESSION['student_id'] === $comment['student_id'] || $_SESSION['student_id'] === '9877389')) {
            $pdo->prepare("DELETE FROM comments WHERE comment_id = ?")->execute([$_POST['comment_id']]);
            header("Location: thread.php?id=" . $comment['thread_id']);
            exit;
        }
    }
}

// 不正なアクセスの場合
header("Location: home.php");
exit;
